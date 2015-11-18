<?php

namespace Juice\UploadBundle\Form;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Juice\UploadBundle\Form\Type\AbstractUploadType;

class GalleryType extends AbstractUploadType
{
    public function __construct(FilterManager $filterManager) {
        $this->filterConfiguration = $filterManager->getFilterConfiguration();
    }

    public function getParent()
    {
        return 'collection';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach($options['field_attr']  as $key => $value) {
            $view->vars['attr'][$key] = $value;
        }

        $this->addVars($view, $options);
        $this->addFilter($view);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'field_attr' => array(),
            'default_data' => array(
                'data-form-kind' => 'image',
                'data-callback' => 'handleGalleryImage',
                'data-crop' => 'false',
            ),
            'options' => array(
                'label' => false,
                'attr' => array(
                    'class' => 'juice_upload_gallery_item',
                )
            ),
            'button_label' => 'Upload',
            'button_class' => '',
            'accept' => '',
            'multi' => true,
            'attr' => array(
                'class' => 'sortable'
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false
        ));
    }

    public function getName()
    {
        return 'juice_upload_gallery_field';
    }
}
