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

    protected function getTmpFolderName()
    {
        return Globals::getTmpUploadDir();
    }

    protected function getFinalFolderName()
    {
        return Globals::getFinalUploadDir();
    }

    protected function getFinalUploadRootDir()
    {
        return __DIR__ . '/../../../../../../web/' . $this->getFinalFolderName();
    }

    protected function getTmpUploadRootDir($path)
    {
        return __DIR__ . '/../../../../../../web/' . $this->getTmpFolderName() . '/' . $path;
    }

    public function removeFile()
    {
        $file = $this->file;
        if (!empty($file) && file_exists($this->getFinalUploadRootDir() . '/' . $file)) {
            //remove old file
            unlink($this->getFinalUploadRootDir() . '/' . $file);
        }
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
        return '/' . $this->getFinalFolderName() . '/' . $this->file;
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function postFlush()
    {
        $this->upload();
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemove()
    {
        $this->removeFile();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set file
     *
     * @param string $file
     * @return Image
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
