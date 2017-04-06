<?php

namespace Jeylabs\Laravelfilemanager\controllers;

use Illuminate\Support\Facades\Storage;
use Jeylabs\Laravelfilemanager\Events\ImageIsRenaming;
use Jeylabs\Laravelfilemanager\Events\ImageWasRenamed;
use Jeylabs\Laravelfilemanager\Events\FolderIsRenaming;
use Jeylabs\Laravelfilemanager\Events\FolderWasRenamed;

/**
 * Class RenameController
 * @package Jeylabs\Laravelfilemanager\controllers
 */
class RenameController extends LfmController
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
            if (Storage::isDirectory($old_file)) {
                return $this->error('folder-name');
            } else {
                return $this->error('file-name');
            }
        }

        if (!Storage::isDirectory($old_file)) {
            $extension = Storage::extension($old_file);
            $new_name = str_replace('.' . $extension, '', $new_name) . '.' . $extension;
        }

        $new_file = parent::getCurrentPath($new_name);

        if (Storage::isDirectory($old_file)) {
            event(new FolderIsRenaming($old_file, $new_file));
        } else {
            event(new ImageIsRenaming($old_file, $new_file));
        }

        if (config('lfm.alphanumeric_directory') && preg_match('/[^\w-]/i', $new_name)) {
            return $this->error('folder-alnum');
        } elseif (Storage::exists($new_file)) {
            return $this->error('rename');
        }

        if (Storage::isDirectory($old_file)) {
            Storage::move($old_file, $new_file);
            event(new FolderWasRenamed($old_file, $new_file));
            return $this->success_response;
        }

        if ($this->fileIsImage($old_file)) {
            Storage::move(parent::getThumbPath($old_name), parent::getThumbPath($new_name));
        }

        Storage::move($old_file, $new_file);

        event(new ImageWasRenamed($old_file, $new_file));

        return $this->success_response;
    }
}
