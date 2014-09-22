<?php

namespace Juice\UploadBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Juice\UploadBundle\Lib\Globals;

/**
 * Video
 */
class SingleFile
{
    protected $temp;

    protected function getTmpFolderName() {
        return Globals::getTmpUploadDir();
    }

    protected function getFinalFolderName() {
        return Globals::getFinalUploadDir();
    }

    protected function getFinalUploadRootDir() {
        return __DIR__ . '/../../../../web/' . $this->getFinalFolderName();
    }

    protected function getTmpUploadRootDir($path) {
        return __DIR__ . '/../../../../web/'. $this->getTmpFolderName() . '/' . $path;
    }

    public function removeFile($file) {
        if (!empty($file) && file_exists($this->getFinalUploadRootDir() . '/' . $file)) {
            //remove old file
            unlink($this->getFinalUploadRootDir() . '/' . $file);
        }
    }

    public function upload($file, $field) {
        $fileName = $file;

        if (empty($file)) {
            $this->{"$field"} = null;
            return;
        }

        if (!file_exists($this->getTmpUploadRootDir($file)) && file_exists($this->getFinalUploadRootDir($file))) {
            //file already uploaded
            $this->{"$field"} = $fileName;
            return;
        }

        if (!$file instanceof File) {
            $file = new File($this->getTmpUploadRootDir($file));
        }

        $file->move($this->getFinalUploadRootDir() , $this->getTmpUploadRootDir($file));

        if (isset($this->temp[$field]) && $this->temp[$field] != $fileName) {
            if (file_exists($this->getFinalUploadRootDir() . '/' . $this->temp[$field])) {
                //remove old file
                unlink($this->getFinalUploadRootDir() . '/' . $this->temp[$field]);
            }
            $this->temp[$field] = null;
        }

        $this->{"$field"} = $fileName;
    }

    /**
     * Set File
     */
    public function setFile($file)
    {
        if (isset($this->file)) {
            // store the old name to delete after the update
            $this->temp['file'] = $this->file;
        }

        $this->file = $file;

        return $this;
    }
}
