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

        $builder
            ->add('file', 'juice_file_type', array(
                'button_label' => $buttonLabel,
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
            'button_label' => 'Upload',
            'field_attr' => array(),
            'data_class' => 'Juice\UploadBundle\Entity\Media',
            'attr' => array(
                'class' => 'juice_upload'
            )
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
