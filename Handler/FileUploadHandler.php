<?php

namespace Juice\UploadBundle\Handler;

class FileUploadHandler extends UploadHandler
{
    public function addTmpFile()
    {
        $result = parent::addTmpFile();

        return $result;
    }
}