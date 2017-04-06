<?php

namespace Jeylabs\Laravelfilemanager\controllers;

/**
 * Class DemoController
 * @package Jeylabs\Laravelfilemanager\controllers
 */
class DemoController extends LfmController
{

    /**
     * @return mixed
     */
    public function index()
    {
        return view('laravel-filemanager::demo');
    }
}
