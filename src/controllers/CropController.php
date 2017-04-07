<?php

namespace Jeylabs\Vaultbox\controllers;


use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Jeylabs\Vaultbox\Events\ImageIsCropping;
use Jeylabs\Vaultbox\Events\ImageWasCropped;

/**
 * Class CropController
 * @package Jeylabs\Vaultbox\controllers
 */
class CropController extends VaultboxController
{
    /**
     * Show crop page
     *
     * @return mixed
     */
    public function getCrop()
    {
        $working_dir = request('working_dir');
        $img = parent::getFileUrl(request('img'));

        return view('vaultbox::crop')
            ->with(compact('working_dir', 'img'));
    }


    /**
     * Crop the image (called via ajax)
     */
    public function getCropimage()
    {
        $image      = request('img');
        $dataX      = request('dataX');
        $dataY      = request('dataY');
        $dataHeight = request('dataHeight');
        $dataWidth  = request('dataWidth');
        $image_path = public_path() . $image;

        // crop image
        $image = Image::make($image_path)
            ->crop($dataWidth, $dataHeight, $dataX, $dataY)
            ->orientate()->encode(pathinfo($image_path)['extension']);
        Storage::disk(config('vaultbox.storage.drive'))->put($image_path, $image->getEncoded());
        event(new ImageIsCropping($image_path));

        // make new thumbnail
        Image::make($image_path)
            ->fit(config('vaultbox.thumb_img_width', 200), config('vaultbox.thumb_img_height', 200))
            ->orientate()->encode(pathinfo(parent::getThumbPath(parent::getName($image_path)))['extension']);
        Storage::disk(config('vaultbox.storage.drive'))->put(parent::getThumbPath(parent::getName($image_path)), $image->getEncoded());
        event(new ImageWasCropped($image_path));
    }
}
