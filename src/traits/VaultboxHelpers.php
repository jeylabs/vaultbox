<?php

namespace Jeylabs\Vaultbox\traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait VaultboxHelpers
{
    /*****************************
     ***       Path / Url      ***
     *****************************/

    private $ds = '/';

    public function getThumbPath($image_name = null)
    {
        return $this->getCurrentPath($image_name, 'thumb');
    }

    public function getCurrentPath($file_name = null, $is_thumb = null)
    {
        $path = $this->composeSegments('dir', $is_thumb, $file_name);
        $path = $this->translateToOsPath($path);

        return $path;
    }

    public function getThumbUrl($image_name = null)
    {
        return $this->getFileUrl($image_name, 'thumb');
    }

    public function getFileUrl($image_name = null, $is_thumb = null)
    {
        return url( config('vaultbox.prefix') . '/' . $this->composeSegments('url', $is_thumb, $image_name));
    }

    private function composeSegments($type, $is_thumb, $file_name)
    {
        $paths = [
            $this->getPathPrefix($type),
            $this->getFormatedWorkingDir(),
            $this->appendThumbFolderPath($is_thumb),
            $file_name
        ];

        $paths = array_filter($paths);
        $full_path = implode($this->ds, $paths);

        $full_path = $this->removeDuplicateSlash($full_path);
        $full_path = $this->translateToVaultboxPath($full_path);

        return $this->removeLastSlash($full_path);
    }

    public function getPathPrefix($type)
    {
        $default_folder_name = 'files';
        if ($this->isProcessingImages()) {
            $default_folder_name = 'photos';
        }

        $prefix = config('vaultbox.' . $this->currentVaultboxType() . 's_folder_name', $default_folder_name);

        if ($type === 'dir') {
            $prefix = config('vaultbox.base_directory', 'public') . '/' . $prefix;
        }

        return $prefix;
    }

    private function getFormatedWorkingDir()
    {
        $working_dir = request('working_dir');

        if (empty($working_dir)) {
            $default_folder_type = 'share';
            if ($this->allowMultiUser()) {
                $default_folder_type = 'user';
            }

            $working_dir = $this->rootFolder($default_folder_type);
        }

        return $this->removeFirstSlash($working_dir);
    }

    private function appendThumbFolderPath($is_thumb)
    {
        if (!$is_thumb) {
            return null;
        }

        $thumb_folder_name = config('vaultbox.thumb_folder_name');
        //if user is inside thumbs folder there is no need
        // to add thumbs substring to the end of $url
        $in_thumb_folder = preg_match('/'.$thumb_folder_name.'$/i', $this->getFormatedWorkingDir());

        if (!$in_thumb_folder) {
            return $thumb_folder_name . $this->ds;
        }
    }

    public function rootFolder($type)
    {
        if ($type === 'user') {
            $folder_name = $this->getUserSlug();
        } else {
            $folder_name = config('vaultbox.shared_folder_name');
        }

        return $this->ds . $folder_name;
    }

    public function getRootFolderPath($type)
    {
        return $this->getPathPrefix('dir') . $this->rootFolder($type);
    }

    public function getName($file)
    {
        $Vaultbox_file_path = $this->getInternalPath($file);

        $arr_dir = explode($this->ds, $Vaultbox_file_path);
        $file_name = end($arr_dir);

        return $file_name;
    }

    public function getInternalPath($full_path)
    {
        $full_path = $this->translateToVaultboxPath($full_path);
        $full_path = $this->translateToUtf8($full_path);
        $Vaultbox_dir_start = strpos($full_path, $this->getPathPrefix('dir'));
        $working_dir_start = $Vaultbox_dir_start + strlen($this->getPathPrefix('dir'));
        $Vaultbox_file_path = $this->ds . substr($full_path, $working_dir_start);

        return $Vaultbox_file_path;
    }

    private function translateToOsPath($path)
    {
        if ($this->isRunningOnWindows()) {
            $path = str_replace($this->ds, '\\', $path);
        }
        return $path;
    }

    private function translateToVaultboxPath($path)
    {
        if ($this->isRunningOnWindows()) {
            $path = str_replace('\\', $this->ds, $path);
        }
        return $path;
    }

    private function removeDuplicateSlash($path)
    {
        return str_replace($this->ds . $this->ds, $this->ds, $path);
    }

    private function removeFirstSlash($path)
    {
        if (starts_with($path, $this->ds)) {
            $path = substr($path, 1);
        }

        return $path;
    }

    private function removeLastSlash($path)
    {
        // remove last slash
        if (ends_with($path, $this->ds)) {
            $path = substr($path, 0, -1);
        }

        return $path;
    }

    public function translateFromUtf8($input)
    {
        if ($this->isRunningOnWindows()) {
            $input = iconv('UTF-8', 'BIG5', $input);
        }

        return $input;
    }

    public function translateToUtf8($input)
    {
        if ($this->isRunningOnWindows()) {
            $input = iconv('BIG5', 'UTF-8', $input);
        }

        return $input;
    }


    /****************************
     ***   Config / Settings  ***
     ****************************/

    public function isProcessingImages()
    {
        return $this->currentVaultboxType() === 'image';
    }

    public function isProcessingFiles()
    {
        return $this->currentVaultboxType() === 'file';
    }

    public function currentVaultboxType($is_for_url = false)
    {
        $file_type = request('type', 'Images');
        if ($is_for_url) {
            return ucfirst($file_type);
        } else {
            return lcfirst(str_singular($file_type));
        }
    }

    public function allowMultiUser()
    {
        return config('vaultbox.allow_multi_user') === true;
    }

    public function enabledShareFolder()
    {
        return config('vaultbox.allow_share_folder') === true;
    }


    /****************************
     ***     File System      ***
     ****************************/

    public function getDirectories($path)
    {
        $thumb_folder_name = config('vaultbox.thumb_folder_name');
        $all_directories = Storage::disk(config('vaultbox.storage.drive'))->directories($path);

        $arr_dir = [];

        foreach ($all_directories as $directory) {
            $directory_name = $this->getName($directory);

            if ($directory_name !== $thumb_folder_name) {
                $arr_dir[] = (object)[
                    'name' => $directory_name,
                    'path' => $this->getInternalPath($directory)
                ];
            }
        }

        return $arr_dir;
    }

    public function getFilesWithInfo($path)
    {
        $arr_files = [];

        foreach (Storage::disk(config('vaultbox.storage.drive'))->files($path) as $key => $file) {
            $file_name = $this->getName($file);

            if ($this->fileIsImage($file)) {
                $file_type = Storage::disk(config('vaultbox.storage.drive'))->mimeType($file);
                $icon = 'fa-image';
            } else {
                $extension = strtolower(File::extension($file_name));
                $file_type = config('vaultbox.file_type_array.' . $extension) ?: 'File';
                $icon = config('vaultbox.file_icon_array.' . $extension) ?: 'fa-file';
            }

            $thumb_url = null;
            $thumb_path = $this->getThumbPath($file_name);
            if (Storage::disk(config('vaultbox.storage.drive'))->exists($thumb_path)) {
                $thumb_url = $this->getThumbUrl($file_name) . '?timestamp=' . Storage::disk(config('vaultbox.storage.drive'))->lastModified($thumb_path);
            }

            $arr_files[$key] = [
                'name'      => $file_name,
                'url'       => $this->getFileUrl($file_name),
                'size'      => $this->humanFilesize(Storage::disk(config('vaultbox.storage.drive'))->size($file)),
                'updated'   => Storage::disk(config('vaultbox.storage.drive'))->lastModified($file),
                'type'      => $file_type,
                'icon'      => $icon,
                'thumb'     => $thumb_url
            ];
        }

        return $arr_files;
    }

    public function createFolderByPath($path)
    {
        if (!Storage::disk(config('vaultbox.storage.drive'))->exists($path)) {
            Storage::disk(config('vaultbox.storage.drive'))->makeDirectory($path, $mode = 0777, true, true);
        }
    }

    public function directoryIsEmpty($directory_path)
    {
        return count(Storage::disk(config('vaultbox.storage.drive'))->allFiles($directory_path)) == 0;
    }

    public function fileIsImage($file)
    {
        if ($file instanceof UploadedFile) {
            $mime_type = $file->getMimeType();
        } else {
            $mime_type = Storage::disk(config('vaultbox.storage.drive'))->mimeType($file);
        }

        return starts_with($mime_type, 'image');
    }


    /****************************
     ***    Miscellaneouses   ***
     ****************************/

    public function getUserSlug()
    {
        $slug_of_user = config('vaultbox.user_field');
        $slug_of_user = empty(auth()->user()) ? '' : auth()->user()->$slug_of_user;
        return $slug_of_user;
    }

    public function error($error_type, $variables = [])
    {
        return trans('vaultbox::vaultbox.error-' . $error_type, $variables);
    }

    public function humanFilesize($bytes, $decimals = 2)
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }

    public function isRunningOnWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
