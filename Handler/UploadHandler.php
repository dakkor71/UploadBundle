<?php

namespace Juice\UploadBundle\Handler;

use Juice\UploadBundle\Lib\Globals;

abstract class UploadHandler
{
    protected $uploadedFile;

    protected $originalFileName;

    protected $tmpFileName;

    protected $files = array();

    protected $remote = false;

    protected $kind = 'file';

    public function __construct($uploadedFile, $originalFileName, array $files, array $post) {
        $this->uploadedFile = $uploadedFile;
        $this->originalFileName = $originalFileName;
        $this->files = $files;
        $this->post = $post;

        $this->setRemoteStatus();
        $this->setTmpFileName();
        $this->addFolders();
    }

    public function __destruct() {
        $this->clearFiles();
    }

    public function addFolders() {
        if (!is_dir($this->getFinalFileFolder())) {
            mkdir($this->getFinalFileFolder());
        }

        if (!is_dir($this->getTmpFileFolder())) {
            mkdir($this->getTmpFileFolder());
        }
    }

    public function addTmpFile() {
        $this->checkFile();
        $this->uploadFile();

        $result = array(
            'success' => true,
            'params' => array(
                'fileName' => $this->tmpFileName,
                'path' => (Globals::getAbsolutePath() ? '/' : '') . $this->getTmpFileFolder() . $this->tmpFileName
            )
        );

        return $result;
    }

    public function getTmpFileFolder() {
        return Globals::getTmpUploadDir() . '/';
    }

    public function getFinalFileFolder() {
        return Globals::getFinalUploadDir() . '/';
    }

    public function getTargetFilePath() {
        $targetPath = Globals::getRootFolder() . $this->getTmpFileFolder();
        return $targetPath . $this->tmpFileName;
    }

    public function checkFile()
    {

        if (isset($this->post['size'])) {
            $requestedSize = explode(',', $this->post['size']);
            list($width, $height) = getimagesize($this->uploadedFile);

            if($width != $requestedSize[0] || $height != $requestedSize[1]) {
                Throw new \Exception('Wrong size. Requested: ' . $requestedSize[0] . 'x' . $requestedSize[1]);
            }
        }

        if (!$this->remote && $this->files['file']['error'] != 0) {
            Throw new \Exception($this->uploadErrorCodeToMessage($this->files['file']['error']));
        }
    }

    /**
     * @throws \Exception
     *
     * upload local file or download remote file. Throws exception on error
     */
    private function uploadFile() {
        if ($this->remote) {
            $status = copy($this->uploadedFile, $this->getTargetFilePath());
        } else {
            $status = move_uploaded_file($this->uploadedFile, $this->getTargetFilePath());
        }

        if (!$status) {
            Throw new \Exception('Error during moving file');
        }
    }

    /**
     * check if file is remove or local
     */
    private function setRemoteStatus() {
        if (empty($this->files)){
            $this->remote = true;
        }
    }

    private function setTmpFileName()
    {
        $fileInfo = pathinfo($this->originalFileName);
        $extension = $fileInfo['extension'];
        $tmpName = hash('sha256', microtime() . rand()) . '.' . $extension;

        while (is_file(Globals::getRootFolder() . $this->getTmpFileFolder() . $tmpName)) {
            $tmpName = hash('sha256', microtime() . rand()) . '.' . $extension;
        }

        $this->tmpFileName = $tmpName;
    }

    private function uploadErrorCodeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }

    /**
     * remove all tmp files which are older that 10h
     */
    protected function clearFiles()
    {
        $excludedFiles = array('.', '..', '.gitignore', '.gitkeep');
        $checkTime = time() - 60 * 60 * 10;
        if ($handle = opendir(Globals::getRootFolder() . $this->getTmpFileFolder())) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry, $excludedFiles) && filemtime(Globals::getRootFolder() . $this->getTmpFileFolder() . $entry) < $checkTime) {
                    $this->deleteTmpFile($entry);
                }
            }

            closedir($handle);
        }
    }

    /**
     * @param $filepath
     *
     * remove tmp file if exist
     */
    public function deleteTmpFile($filepath)
    {
        if (is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder() . $filepath)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder() . $filepath);
        }
    }
}