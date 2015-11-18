<?php

namespace Juice\UploadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

use Juice\UploadBundle\Lib\Globals;

class AbstractUploadType extends AbstractType
{

    /**
     * @var FilterConfiguration
     */
    protected $filterConfiguration;

    public function getParent()
    {
        return 'text';
    }

    public function addVars(&$view, $options) {
        $view->vars['button_label'] = $options['button_label'];
        $view->vars['button_class'] = $options['button_class'];
        $view->vars['accept'] = $options['accept'];
        $view->vars['multi'] = isset($options['multi']) ? $options['multi'] : false;

        foreach($options['default_data']  as $key => $value) {
            if(!isset($view->vars['attr'][$key])) {
                $view->vars['attr'][$key] = $value;
            }
        }

        $view->vars['tmpFolder'] = (Globals::getAbsolutePath() ? '/' : '') . Globals::getTmpUploadDir();
        $view->vars['finalFolder'] = (Globals::getAbsolutePath() ? '/' : '') . Globals::getFinalUploadDir();
    }

    protected function addFilter(&$view) {
        if (!isset($view->vars['attr']['filter']) || empty($view->vars['attr']['filter'])) {
            throw new NotFoundHttpException("You need to define filter name");
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
                'data-form-kind' => 'file',
                'data-callback' => 'handleFile',
                'data-crop' => 'false'
            ),
            'button_label' => '',
            'button_class' => '',
            'accept' => '',
            'multi' => false
        ));
    }

    public function getName()
    {
        return 'upload_type';
    }
}