<?php

namespace Jeylabs\Vaultbox\controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Jeylabs\Vaultbox\Events\ImageIsRenaming;
use Jeylabs\Vaultbox\Events\ImageWasRenamed;
use Jeylabs\Vaultbox\Events\FolderIsRenaming;
use Jeylabs\Vaultbox\Events\FolderWasRenamed;

/**
 * Class RenameController
 * @package Jeylabs\Vaultbox\controllers
 */
class RenameController extends VaultboxController
{
    /**
     * @return string
     */
    public function getRename()
    {
        $old_name = $this->translateFromUtf8(request('file'));
        $new_name = $this->translateFromUtf8(trim(request('new_name')));

        $old_file = parent::getCurrentPath($old_name);

        if (empty($new_name)) {
            if (File::isDirectory($old_file)) {
                return $this->error('folder-name');
            } else {
                return $this->error('file-name');
            }
        }

        if (!File::isDirectory($old_file)) {
            $extension = File::extension($old_file);
            $new_name = str_replace('.' . $extension, '', $new_name) . '.' . $extension;
        }

        $new_file = parent::getCurrentPath($new_name);

        if (File::isDirectory($old_file)) {
            event(new FolderIsRenaming($old_file, $new_file));
        } else {
            event(new ImageIsRenaming($old_file, $new_file));
        }

        if (config('vaultbox.alphanumeric_directory') && preg_match('/[^\w-]/i', $new_name)) {
            return $this->error('folder-alnum');
        } elseif (Storage::disk(config('vaultbox.storage.drive'))->exists($new_file)) {
            return $this->error('rename');
        }


        if (File::isDirectory($old_file)) {
            Storage::disk(config('vaultbox.storage.drive'))->move($old_file, $new_file);
            event(new FolderWasRenamed($old_file, $new_file));
            return $this->success_response;
        }

        if ($this->fileIsImage($old_file)) {
            Storage::disk(config('vaultbox.storage.drive'))->move(parent::getThumbPath($old_name), parent::getThumbPath($new_name));
        }

        Storage::disk(config('vaultbox.storage.drive'))->move($old_file, $new_file);

        event(new ImageWasRenamed($old_file, $new_file));

        return $this->success_response;
    }
}
