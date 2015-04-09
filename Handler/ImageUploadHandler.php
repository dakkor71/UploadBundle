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

        if (!$this->isImage()) {
//            $this->deleteTmpFile($result['params']['filename']);
//            Throw new \Exception("Uploaded file is not image");
        }



        return $result;
    }

    public function isImage() {
        $file = new File($this->getTargetFilePath());

        $mimeType = $file->getMimeType();

        if (strpos($mimeType, 'image')) {
            return TRUE;
        }

        return FALSE;
    }
}