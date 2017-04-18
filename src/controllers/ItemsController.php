<?php

namespace Jeylabs\Vaultbox\controllers;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\NotFoundException;


/**
 * Class ItemsController
 * @package Jeylabs\Vaultbox\controllers
 */
class ItemsController extends VaultboxController
{
    /**
     * Get the images to load for a selected folder
     *
     * @return mixed
     */
    public function getItems()
    {
        $path = $this->getCurrentPath();
        return [
            'html' => (string) view($this->getView())->with([
                'files' => $this->getFilesWithInfo($path),
                'directories' => $this->getDirectories($path)
            ]),
            'working_dir' => $this->getInternalPath($path)
        ];
    }


    private function getView()
    {
        $view_type = 'grid';
        $show_list = request('show_list');

        if ($show_list === "1") {
            $view_type = 'list';
        } elseif (is_null($show_list)) {
            $type_key = $this->currentVaultboxType();
            $startup_view = config('vaultbox.' . $type_key . 's_startup_view');

            if (in_array($startup_view, ['list', 'grid'])) {
                $view_type = $startup_view;
            }
        }

        return 'vaultbox::' . $view_type . '-view';
    }

    public function fileShow($file)
    {
        if(!Storage::disk(config('vaultbox.storage.drive'))->exists('/' . config('vaultbox.base_directory') . '/'. $file)) {
            throw new NotFoundException();
        }

        $mine = Storage::disk(config('vaultbox.storage.drive'))->mimeType(config('vaultbox.base_directory') . '/'. $file);
        $fileContent = Storage::disk(config('vaultbox.storage.drive'))->get(config('vaultbox.base_directory') . '/'. $file);
        return response($fileContent)->header('Content-Type',  $mine);
    }
}
