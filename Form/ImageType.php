<?php

namespace Juice\UploadBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImageType extends AbstractFieldType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::setField($builder, $options, 'juice_upload_image_type');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'accept' => '*.jpg;*.png'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'juice_upload_image_field';
    }
}
