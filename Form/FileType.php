<?php

namespace Juice\UploadBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FileType extends AbstractType
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
            ->add('file', 'juice_file_type', array(
                'label' => false,
                'button_label' => $buttonLabel,
                'attr' => $attr,
                'accept' => $accept
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'error_bubbling' => false,
            'required' => false,
            'by_reference' => false,
            'data_class' => 'Juice\UploadBundle\Entity\Media',
            'attr' => array(
                'class' => 'juice_upload'
            ),

            'button_label' => 'Upload',
            'accept' => '',
            'field_attr' => array(),
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'juice_single_file_field';
    }
}
