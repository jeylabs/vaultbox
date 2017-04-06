<?php

namespace Jeylabs\Vaultbox\controllers;

/**
 * Class DemoController
 * @package Jeylabs\Vaultbox\controllers
 */
class DemoController extends VaultboxController
{

    /**
     * @return mixed
     */
    public function index()
    {
        return view('vaultbox::demo');
    }
}
