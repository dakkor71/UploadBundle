<?php

namespace Juice\UploadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Juice\UploadBundle\Lib\Globals;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

/**
 * Image
 *
 * @ORM\Table(name="media")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Media
{
    protected $temp;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=255)
     */
    protected $file;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    protected function getFinalFolderName()
    {
        return Globals::getFinalUploadDir();
    }

    protected function getFinalUploadRootDir()
    {
        return Globals::getFinalUploadRootDir();
    }

    protected function getTmpUploadRootDir($path)
    {
        return Globals::getTmpUploadRootDir($path);
    }

    public function removeFile($file)
    {
        if (!empty($file) && file_exists($this->getFinalUploadRootDir() . '/' . $file)) {
            unlink($this->getFinalUploadRootDir() . '/' . $file);
        }
    }

    public function clearFiles() {
        $this->removeFile($this->file);
        $this->removeFile($this->temp['file']);
    }

    public function upload()
    {
        $file = $this->file;
        $field = 'file';
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

        if (!$file instanceof SymfonyFile) {
            $file = new SymfonyFile($this->getTmpUploadRootDir($file));
        }

        $file->move($this->getFinalUploadRootDir(), $this->getTmpUploadRootDir($file));

        if (isset($this->temp[$field])) {
            if (!empty($this->temp[$field]) && file_exists($this->getFinalUploadRootDir() . '/' . $this->temp[$field])) {
                //remove old file
                unlink($this->getFinalUploadRootDir() . '/' . $this->temp[$field]);
            }
            $this->temp[$field] = null;
        }

        $this->{"$field"} = $fileName;
    }

    /*
     * //////////////////////////////////////////////////////
     */

    public function getWebPath()
    {
        if (empty($this->file)) {
            return null;
        }
        return (Globals::getAbsolutePath() ? '/' : '') . $this->getFinalFolderName() . '/' . $this->file;
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function postFlush()
    {
        $this->upload();

        if ($this->file == null) {
            $this->clearFiles();
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        $this->clearFiles();
    }

    /**
     * Set file
     *
     * @param string $file
     * @return Image
     */
    public function setFile($file)
    {
        if ($this->file != $file) {
            // store the old name to delete after the update
            $this->temp['file'] = $this->file;
        }


        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }
}