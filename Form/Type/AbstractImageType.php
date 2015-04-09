<?php

namespace Juice\UploadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AbstractImageType extends AbstractType
{
    /**
     * @var FilterConfiguration
     */
    private $filterConfiguration;

    public function __construct(FilterManager $filterManager) {
        $this->filterConfiguration = $filterManager->getFilterConfiguration();
    }

    public function getParent()
    {
        return 'text';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!isset($view->vars['attr']['filter'])) {
            throw new NotFoundHttpException("You need to define filter name");
        }

        $view->vars['button_label'] = $options['button_label'];
        $view->vars['upload_class'] = $options['upload_class'];
        $view->vars['accept'] = $options['accept'];

        foreach($options['default_data']  as $key => $value) {
            if(!isset($view->vars['attr'][$key])) {
                $view->vars['attr'][$key] = $value;
            }
        }

        $filter = $view->vars['attr']['filter'];
        $config = $this->filterConfiguration->get($filter);
        list($width , $heigth) = $config['filters']['thumbnail']['size'];
        $ratio = $width / $heigth;
        $minSize = '({width : ' . $width . ',height : ' . $heigth . '})';

        $view->vars['attr']['data-ratio'] = $ratio;
        $view->vars['attr']['data-minSize'] = $minSize;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'default_data' => array(
                'data-form-kind' => 'image',
                'data-callback' => 'handleSingleImage',
                'data-crop' => 'false',
            ),
            'upload_class' => 'juice_upload',
            'button_label' => 'Upload',
            'accept' => '.jpg, .png'
        ));
    }

    public function getName()
    {
        return 'juice_image_type';
    }
}