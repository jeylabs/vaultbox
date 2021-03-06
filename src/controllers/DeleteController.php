<?php

namespace Jeylabs\Vaultbox\controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Jeylabs\Vaultbox\Events\ImageIsDeleting;
use Jeylabs\Vaultbox\Events\ImageWasDeleted;

/**
 * Class CropController
 * @package Jeylabs\Vaultbox\controllers
 */
class DeleteController extends VaultboxController
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

        if (!Storage::disk(config('vaultbox.storage.drive'))->exists($file_to_delete)) {
            return $this->error('folder-not-found', ['folder' => $file_to_delete]);
        }

        if(!parent::directoryIsEmpty($file_to_delete)) {
            Storage::disk(config('vaultbox.storage.drive'))->deleteDirectory($file_to_delete);
            return $this->success_response;
        }

        if(File::isDirectory($file_to_delete)) {
            Storage::disk(config('vaultbox.storage.drive'))->deleteDirectory($file_to_delete);
        }

        if ($this->fileIsImage($file_to_delete)) {
            Storage::disk(config('vaultbox.storage.drive'))->delete($thumb_to_delete);
        }

        Storage::disk(config('vaultbox.storage.drive'))->delete($file_to_delete);

        event(new ImageWasDeleted($file_to_delete));

        return $this->success_response;
    }
}
