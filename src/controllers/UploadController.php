<?php

namespace Jeylabs\Vaultbox\controllers;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Jeylabs\Vaultbox\Events\ImageIsUploading;
use Jeylabs\Vaultbox\Events\ImageWasUploaded;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UploadController
 * @package Jeylabs\Vaultbox\controllers
 */
class UploadController extends VaultboxController
{
    /**
     * Upload an image/file and (for images) create thumbnail
     * @return string
     * @internal param UploadRequest $request
     */
    public function upload()
    {
        $response = [];
        $files = request()->file('upload');
        $error_bag = [];

        foreach (is_array($files) ? $files : [$files] as $file) {
			$time = time();
            $validation_message = $this->uploadValidator($file, $time);
            $new_filename = $this->proceedSingleUpload($file, $time);

            if ($validation_message !== 'pass') {
                array_push($error_bag, $validation_message);
            } elseif ($new_filename == 'invalid') {
                array_push($error_bag, $response);
            }
        }

        if (is_array($files)) {
            $response = count($error_bag) > 0 ? $error_bag : $this->success_response;
        } else { // upload via ckeditor 'Upload' tab
            $response = $this->useFile($new_filename);
        }

        return $response;
    }

    private function proceedSingleUpload($file, $time)
    {
        $validation_message = $this->uploadValidator($file, $time);
        if ($validation_message !== 'pass') {
            return $validation_message;
        }

        $new_filename  = $this->getNewName($file);
		$rootPath = config('vaultbox.storage.root');
        $new_file_path = parent::getCurrentPath() . '/' . $time . '/' ;
        if(!Storage::disk(config('vaultbox.storage.drive'))->exists($new_file_path)) {
            Storage::disk(config('vaultbox.storage.drive'))->makeDirectory($new_file_path);
        }

        $filePath = $new_file_path . $new_filename;
        event(new ImageIsUploading($filePath));

        try {
            if ($this->fileIsImage($file)) {
                $tempPath = $new_file_path . config('vaultbox.thumb_folder_name') . '/';
                $tempFilePath = $tempPath . $new_filename;

                if(!Storage::disk(config('vaultbox.storage.drive'))->exists($tempPath)) {
                    Storage::disk(config('vaultbox.storage.drive'))->makeDirectory($tempPath);
                }

                $image = Image::make($file->getRealPath())
                    ->orientate()->encode(pathinfo($filePath)['extension']);
                Storage::disk(config('vaultbox.storage.drive'))->put($filePath, $image->getEncoded());

                $image = Image::make($file->getRealPath())
                    ->fit(config('vaultbox.thumb_img_width', 200), config('vaultbox.thumb_img_height', 200))
                    ->encode(pathinfo($tempFilePath)['extension']);
                Storage::disk(config('vaultbox.storage.drive'))->put($tempFilePath, $image->getEncoded());

                chmod($rootPath . '/' . $filePath, 0777);
                chmod($rootPath . '/' . $tempFilePath, 0777);

            } else {
                Storage::disk(config('vaultbox.storage.drive'))
                    ->putFileAs(str_replace($new_filename, '', $new_file_path) ,$file, $new_filename);
                chmod($rootPath . '/' . $filePath, 0777);
            }
        } catch (\Exception $e) {
            return $this->error('invalid');
        }

        event(new ImageWasUploaded(realpath($time . '/' . $new_filename)));
        return $time . '/' . $new_filename;
    }

    private function uploadValidator($file, $time)
    {
        if (empty($file)) {
            return $this->error('file-empty');
        } elseif (!$file instanceof UploadedFile) {
            return $this->error('instance');
        } elseif ($file->getError() == UPLOAD_ERR_INI_SIZE) {
            $max_size = ini_get('upload_max_filesize');
            return $this->error('file-size', ['max' => $max_size]);
        } elseif ($file->getError() != UPLOAD_ERR_OK) {
            return 'File failed to upload. Error code: ' . $file->getError();
        }

        $new_filename = $time . '/' . $this->getNewName($file);
        if (Storage::disk(config('vaultbox.storage.drive'))->exists(parent::getCurrentPath( $new_filename))) {
            return $this->error('file-exist');
        }

        $mimetype = $file->getMimeType();

        // size to kb unit is needed
        $file_size = $file->getSize() / 1000;
        $type_key = $this->currentVaultboxType();

        if (config('vaultbox.should_validate_mime')) {
            $mine_config = 'vaultbox.valid_' . $type_key . '_mimetypes';
            $valid_mimetypes = config($mine_config, []);
            if (false === in_array($mimetype, $valid_mimetypes)) {
                return $this->error('mime') . $mimetype;
            }
        }

        if (config('vaultbox.should_validate_size')) {
            $max_size = config('vaultbox.max_' . $type_key . '_size', 0);
            if ($file_size > $max_size) {
                return $this->error('size') . $mimetype;
            }
        }

        return 'pass';
    }

    private function getNewName($file)
    {
        $new_filename = $this->translateFromUtf8(trim(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)));

        if (config('vaultbox.rename_file') === true) {
            $new_filename = uniqid();
        } elseif (config('vaultbox.alphanumeric_filename') === true) {
            $new_filename = preg_replace('/[^A-Za-z0-9\-\']/', '_', $new_filename);
        }

        if ($file->getClientOriginalExtension()) {
            return  $new_filename . '.' . $file->getClientOriginalExtension();
        }
        return $new_filename;
    }

    private function makeThumb($file, $new_filename)
    {
        // create thumb folder
        $this->createFolderByPath(parent::getThumbPath());

        // create thumb image
        $image = Image::make($file->getRealPath())
            ->fit(config('vaultbox.thumb_img_width', 200), config('vaultbox.thumb_img_height', 200))
            ->encode(pathinfo($new_filename)['extension']);
        Storage::disk(config('vaultbox.storage.drive'))->put(parent::getThumbPath($new_filename), $image->getEncoded());
    }

    private function useFile($new_filename)
    {
        $file = parent::getFileUrl($new_filename);

        return "<script type='text/javascript'>

        function getUrlParam(paramName) {
            var reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i');
            var match = window.location.search.match(reParam);
            return ( match && match.length > 1 ) ? match[1] : null;
        }

        var funcNum = getUrlParam('CKEditorFuncNum');

        var par = window.parent,
            op = window.opener,
            o = (par && par.CKEDITOR) ? par : ((op && op.CKEDITOR) ? op : false);

        if (op) window.close();
        if (o !== false) o.CKEDITOR.tools.callFunction(funcNum, '$file');
        </script>";
    }
}
