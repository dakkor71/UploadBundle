<?php

namespace Juice\UploadBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Sluggable;
use Doctrine\Common\Collections\ArrayCollection;
use Juice\UploadBundle\Entity\CollectionItem as CollectionItem2;

class Collection
{
    protected $id;

    /**
     * @ORM\Column(type="string", name="title")
     */
    protected $title;

    /**
     * @Gedmo\Slug(fields={"title"}, updatable=true, separator="_")
     * @ORM\Column(length=32, unique=true)
     */
    protected $slug;

    protected $items;

    /**
     * Get id
     *
     * @return integer
     */
    protected function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Gallery
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getItems()
    {
        return $this->items;
    }


    public function removeItem($item)
    {
        $this->items->removeElement($item);
    }

    public function setPosition($position)
    {
        $this->position = $position;

        return $position;
    }
}
