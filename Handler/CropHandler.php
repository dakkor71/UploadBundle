<?php

namespace Juice\UploadBundle\Handler;

use Juice\UploadBundle\Lib\Globals;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;

class CropHandler
{

    public function cropImage($coordinates, $fileName)
    {
        //prepare coordinates
        if ($coordinates['x'] < 0) {
            $coordinates['x'] = 0;
        }

        if ($coordinates['y'] < 0) {
            $coordinates['y'] = 0;
        }

        $imagine = new Imagine();
        $image = $imagine
            ->open(Globals::getTmpUploadDir() . '/' . $fileName);

        $image
            ->crop(new Point($coordinates['x'], $coordinates['y']), new Box($coordinates['w'], $coordinates['h']))
            ->save(Globals::getTmpUploadDir() . '/' . $fileName);

        return $result = array(
            'status' => 'success'
        );
    }
}
