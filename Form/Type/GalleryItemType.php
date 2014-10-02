<?php

namespace Juice\UploadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class GalleryItemType extends AbstractType
{

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'juice_gallery_item_type';
    }
}