<?php

namespace Juice\UploadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AbstractFileType extends AbstractType
{

    public function getParent()
    {
        return 'text';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['button_label'] = $options['button_label'];
        $view->vars['upload_class'] = $options['upload_class'];
        $view->vars['attr']['data-callback'] = $options['callback'];
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'upload_class' => 'juice_upload',
            'button_label' => 'Upload',
            'callback' => 'handleSingleFile'
        ));
    }


    public function getName()
    {
        return 'juice_file_type';
    }
}