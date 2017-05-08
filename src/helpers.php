<?php

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\NotFoundException;

if (! function_exists('vaultbox_file_path')) {
    /**
     * Get a path for the file.
     *
     * @param  string $path
     * @param bool $fullPath
     * @param null $extraRemoves
     * @return string
     */
    function vaultbox_file_path($path, $fullPath = false, $extraRemoves = null)
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

        $imagePath = config('vaultbox.base_directory') . '/' . config('vaultbox.images_folder_name') . $path;
        if(Storage::disk(config('vaultbox.storage.drive'))->exists($imagePath)) {
            $path = $fullPath ? $root . $imagePath : $imagePath;
            return $path;
        }

        $filePath = config('vaultbox.base_directory') . '/' . config('vaultbox.files_folder_name') . $path;
        if(Storage::disk(config('vaultbox.storage.drive'))->exists($filePath)) {
            $path = $fullPath ? $root . $imagePath : $imagePath;
            return $path;
        }

        return false;
    }
}