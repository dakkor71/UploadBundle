<?php

namespace Juice\UploadBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BaseGalleryType extends AbstractUploadType
{
    public function getParent()
    {
        return 'collection';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        dump($options);
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
                'data-callback' => 'handleGalleryImage',
                'data-crop' => 'false',
            ),
            'options' => array(
                'label' => false
            ),
            'upload_class' => 'juice_upload',
            'button_label' => 'Upload',
            'accept' => '',
            'multi' => true,
            'attr' => array(
                'class' => 'sortable'
            ),
            'allow_add' => true,
            'allow_delete' => true,
        ));
    }

    public function getName()
    {
        return 'juice_gallery_field';
    }
}
