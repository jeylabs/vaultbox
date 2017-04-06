<?php

namespace Jeylabs\Vaultbox\controllers;

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
        return response()->download(parent::getCurrentPath(request('file')));
    }
}
