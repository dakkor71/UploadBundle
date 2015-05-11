<?php

namespace Juice\UploadBundle\Form\Type;


use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BaseFileType extends AbstractUploadType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->addVars($view, $options);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'default_data' => array(
                'data-form-kind' => 'file',
                'data-callback' => 'handleSingleFile',
                'data-crop' => 'false',
            ),
            'upload_class' => 'juice_upload',
            'button_label' => 'Upload',
            'accept' => '',

        ));
    }


    public function getName()
    {
        return 'juice_file_type';
    }
}