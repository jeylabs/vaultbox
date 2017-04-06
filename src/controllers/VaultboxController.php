<?php

namespace Jeylabs\Vaultbox\controllers;

use Jeylabs\Vaultbox\traits\VaultboxHelpers;

/**
 * Class VaultboxController
 * @package Jeylabs\Vaultbox\controllers
 */
class VaultboxController extends Controller
{
    use VaultboxHelpers;

    protected $success_response = 'OK';

    public function __construct()
    {
        if (!$this->isProcessingImages() && !$this->isProcessingFiles()) {
            throw new \Exception('unexpected type parameter');
        }
    }

    /**
     * Show the filemanager
     *
     * @return mixed
     */
    public function show()
    {
        return view('vaultbox::index');
    }

    public function getErrors()
    {
        $arr_errors = [];

        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            array_push($arr_errors, trans('vaultbox::vaultbox.message-extension_not_found'));
        }

        $type_key = $this->currentVaultboxType();
        $mine_config = 'vaultbox.valid_' . $type_key . '_mimetypes';
        $config_error = null;

        if (!is_array(config($mine_config))) {
            array_push($arr_errors, 'Config : ' . $mine_config . ' is not a valid array.');
        }

        return $arr_errors;
    }
}
