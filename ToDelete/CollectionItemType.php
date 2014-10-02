<?php

namespace Juice\UploadBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CollectionItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('photo', null, array(
                'attr' => array(
                    'class' => 'photo'

                )
            ))
            ->add('position', null, array(
                'data' => 0,
                'attr' => array(
                    'class' => 'position'
                )
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Juice\UploadBundle\Entity\CollectionItem',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
        ));
    }

    public function getName()
    {
        return 'gallery_item_type';
    }
}
