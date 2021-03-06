<?php

namespace Juice\UploadBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class BaseImageType extends AbstractUploadType
{
    public function __construct(FilterManager $filterManager) {
        $this->filterConfiguration = $filterManager->getFilterConfiguration();
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->addVars($view, $options);
        $this->addFilter($view);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'default_data' => array(
                'data-form-kind' => 'image',
                'data-callback' => 'handleImage'
            )
        ));
    }

    public function getName()
    {
        return 'juice_upload_image_type';
    }
}