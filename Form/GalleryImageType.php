<?php

namespace Juice\UploadBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GalleryImageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attr = $options['field_attr'];
        $buttonLabel = $options['button_label'];
        $accept = $options['accept'];

        $builder
            ->add('file', 'juice_gallery_image_type', array(
                'label' => false,
                'button_label' => $buttonLabel,
                'accept' => $accept,
                'attr' => $attr
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label' => false,
            'by_reference' => false,
            'required' => false,
            'data_class' => 'Juice\UploadBundle\Entity\Media',
            'attr' => array(
                'class' => 'juice_upload'
            ),

            'upload_class' => 'juice_upload',
            'button_label' => 'Upload',
            'accept' => '.jpg, .png',
            'field_attr' => array()
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'juice_gallery_child_field';
    }
}
