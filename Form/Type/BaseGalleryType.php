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
        $this->addVars($view, $options);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'by_reference' => false,
            'default_data' => array(
                'data-form-kind' => 'file',
                'data-callback' => 'handleGalleryImage',
                'data-crop' => 'false',
            ),
            'upload_class' => 'juice_upload',
            'button_label' => 'Upload',
            'accept' => '',
            'multi' => false
        ));
    }

    public function getName()
    {
        return 'juice_gallery_field';
    }
}
