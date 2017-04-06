<?php

namespace Jeylabs\Laravelfilemanager\controllers;

use Illuminate\Support\Facades\Storage;
use Jeylabs\Laravelfilemanager\Events\ImageIsDeleting;
use Jeylabs\Laravelfilemanager\Events\ImageWasDeleted;

/**
 * Class CropController
 * @package Jeylabs\Laravelfilemanager\controllers
 */
class DeleteController extends LfmController
{
    /**
     * Delete image and associated thumbnail
     *
     * @return mixed
     */
    public function getDelete()
    {
        $name_to_delete = request('items');

        $file_to_delete = parent::getCurrentPath($name_to_delete);
        $thumb_to_delete = parent::getThumbPath($name_to_delete);

        event(new ImageIsDeleting($file_to_delete));

        if (is_null($name_to_delete)) {
            return $this->error('folder-name');
        }

        if (!Storage::exists($file_to_delete)) {
            return $this->error('folder-not-found', ['folder' => $file_to_delete]);
        }

        if (Storage::isDirectory($file_to_delete)) {
            if (!parent::directoryIsEmpty($file_to_delete)) {
                return $this->error('delete-folder');
            }

            Storage::deleteDirectory($file_to_delete);

            return $this->success_response;
        }

        if ($this->fileIsImage($file_to_delete)) {
            Storage::delete($thumb_to_delete);
        }

        Storage::delete($file_to_delete);

        event(new ImageWasDeleted($file_to_delete));

        return $this->success_response;
    }
}
