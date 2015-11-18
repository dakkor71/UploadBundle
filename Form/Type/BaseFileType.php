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

    public function getName()
    {
        return 'juice_upload_file_type';
    }
}