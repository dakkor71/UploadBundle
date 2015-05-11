<?php

namespace Juice\UploadBundle\Handler;

use Symfony\Component\HttpFoundation\File\File;

class ImageUploadHandler extends UploadHandler
{
    public function addTmpFile()
    {

        $result = parent::addTmpFile();

        list($width, $height) = getimagesize($this->getTargetFilePath());

        $result['params']['size'] = array(
            'width' => $width,
            'height' => $height
        );

        return $result;
    }
}