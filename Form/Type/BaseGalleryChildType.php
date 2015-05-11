<?php

namespace Juice\UploadBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class BaseGalleryChildType extends AbstractUploadType
{
    public function __construct(FilterManager $filterManager) {
        $this->filterConfiguration = $filterManager->getFilterConfiguration();
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
            'default_data' => array(
                'data-form-kind' => 'file',
                'data-callback' => 'handleGalleryImage',
                'data-crop' => 'false',
            ),
            'upload_class' => 'juice_upload',
            'button_label' => 'Upload',
            'accept' => '',
            'multi' => true
        ));
    }

    public function getName()
    {
        return 'juice_gallery_image_type';
    }
}
