<?php

namespace Juice\UploadBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AbstractFieldType extends AbstractType
{
    public function setField(FormBuilderInterface &$builder, array $options, $field)
    {
        $builder
            ->add('file', $field, array(
                'label' => false,
                'button_label' => $options['button_label'],
                'button_class' => $options['button_class'],
                'attr' => $options['field_attr'],
                'accept' => $options['accept']
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Juice\UploadBundle\Entity\Media',
            'button_label' => 'Upload',
            'button_class' => '',
            'accept' => '',
            'error_bubbling' => false,
            'required' => false,
            'by_reference' => false,
            'field_attr' => array()
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
