<?php

namespace Juice\UploadBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Component\HttpFoundation\File\File;
use Juice\UploadBundle\Lib\Globals;

abstract class CollectionItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="photo")
     */
    protected $photo;

    /**
     * @ORM\Column(type="integer", name="position")
     */
    protected $position;

    protected $collection;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function setPhoto($photo)
    {
        if (isset($this->photo)) {
            // store the old name to delete after the update
            $this->temp['photo'] = $this->photo;
        }

        $this->photo = $photo;

        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function getPath()
    {
        return '/media/' . $this->photo;
    }

    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    public function getPosition()
    {
        return $this->position;
    }

    /*
    * //////////////////////////////////////////////////////
    */

    protected $temp;

    protected function getTmpFolderName() {
        return Globals::getTmpUploadDir();
    }

    protected function getFinalFolderName() {
        return Globals::getFinalUploadDir();
    }

    protected function getFinalUploadRootDir() {
        return __DIR__ . '/../../../../../../web/' . $this->getFinalFolderName();
    }

    protected function getTmpUploadRootDir($path) {
        return __DIR__ . '/../../../../../../web/'. $this->getTmpFolderName() . '/' . $path;
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

        if (isset($this->temp[$field])) {
            if (file_exists($this->getFinalUploadRootDir() . '/' . $this->temp[$field])) {
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

    public function getPhotoPathForWeb() {
        return '/' . $this->getFinalFolderName() . '/'. $this->photo;
    }

    public function getVideoWithPath() {
        return '/' . $this->videoPath;
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function preFlush() {
        $this->upload($this->photo , 'photo');
    }

    /**
     * @ORM\PostRemove()
     */
    public function postRemove() {
        $this->removeFile($this->photo , 'photo');
    }

    /*
     * //////////////////////////////////////////////////////
     */
}
