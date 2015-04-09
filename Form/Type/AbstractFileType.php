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
        $view->vars['accept'] = $options['accept'];

        foreach($options['default_data']  as $key => $value) {
            if(!isset($view->vars['attr'][$key])) {
                $view->vars['attr'][$key] = $value;
            }
        }
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
            'accept' => ''
        ));
    }


    public function getName()
    {
        return 'juice_file_type';
    }
}