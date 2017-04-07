<?php

namespace Jeylabs\Vaultbox\controllers;


use Illuminate\Support\Facades\Storage;

/**
 * Class DownloadController
 * @package Jeylabs\Vaultbox\controllers
 */
class DownloadController extends VaultboxController
{
    /**
     * Download a file
     *
     * @return mixed
     */
    public function getDownload()
    {
        $fileName = request('file');
        $fileContent = Storage::disk(config('vaultbox.storage.drive'))->get(parent::getCurrentPath($fileName));
        $mimeType = Storage::disk(config('vaultbox.storage.drive'))->mimeType(parent::getCurrentPath(request('file')));
        $response = response($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Content-Transfer-Encoding' => 'binary',
        ]);
        ob_end_clean();
        return $response;
    }
}
