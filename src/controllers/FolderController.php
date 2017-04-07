<?php

namespace Jeylabs\Vaultbox\controllers;

use Illuminate\Support\Facades\Storage;

/**
 * Class FolderController
 * @package Jeylabs\Vaultbox\controllers
 */
class FolderController extends VaultboxController
{
    /**
     * Get list of folders as json to populate treeview
     *
     * @return mixed
     */
    public function getFolders()
    {
        $folder_types = [];
        $root_folders = [];

        if (parent::allowMultiUser()) {
            $folder_types['user'] = 'root';
        }

        if ((parent::allowMultiUser() && parent::enabledShareFolder()) || !parent::allowMultiUser()) {
            $folder_types['share'] = 'shares';
        }

        foreach ($folder_types as $folder_type => $lang_key) {
            $root_folder_path = parent::getRootFolderPath($folder_type);

            array_push($root_folders, (object)[
                'name' => trans('vaultbox::vaultbox.title-' . $lang_key),
                'path' => parent::getInternalPath($root_folder_path),
                'children' => parent::getDirectories($root_folder_path),
                'has_next' => !($lang_key == end($folder_types))
            ]);
        }

        return view('vaultbox::tree')
            ->with(compact('root_folders'));
    }


    /**
     * Add a new folder
     *
     * @return mixed
     */
    public function getAddfolder()
    {
        $folder_name = $this->translateFromUtf8(trim(request('name')));

        $path = parent::getCurrentPath($folder_name);

        if (empty($folder_name)) {
            return $this->error('folder-name');
        } elseif (Storage::disk(config('vaultbox.storage.drive'))->exists($path)) {
            return $this->error('folder-exist');
        } elseif (config('vaultbox.alphanumeric_directory') && preg_match('/[^\w-]/i', $folder_name)) {
            return $this->error('folder-alnum');
        } else {
            $this->createFolderByPath($path);
            return $this->success_response;
        }
    }
}
