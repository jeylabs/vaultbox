<?php

use Intervention\Image\Exception\NotFoundException;
use Jeylabs\Vaultbox\controllers\VaultboxController;

if (! function_exists('vaultbox_file_path')) {
    /**
     * Get a path for the file.
     *
     * @param  string $path
     * @param null $extraRemoves
     * @return string
     */
    function vaultbox_file_path($path, $extraRemoves = null)
    {
        if (is_null($path)) {
            throw new NotFoundException();
        }

        $root = config('vaultbox.storage.root', storage_path()) . '/';

        $path = str_replace(config('vaultbox.prefix'), null, $path);
        $path = trim($path, '/');

        $path = str_replace(config('vaultbox.images_folder_name'), null, $path);
        $path = trim($path, '/');

        $path = str_replace(config('vaultbox.files_folder_name'), null, $path);
        $path = trim($path, '/');

        if($extraRemoves) {
            $path = str_replace($extraRemoves, null, $path);
        }

        $controller = new VaultboxController();
        $root .= $controller->getCurrentPath($path);

        return $root;
    }
}