<?php

namespace Juice\UploadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $view->vars['accept'] = $options['accept'];
        $view->vars['multi'] = isset($options['multi']) ? $options['multi'] : false;

        foreach($options['default_data']  as $key => $value) {
            if(!isset($view->vars['attr'][$key])) {
                $view->vars['attr'][$key] = $value;
            }
        }

        $view->vars['tmpFolder'] = Globals::getTmpUploadDir();
        $view->vars['finalFolder'] = Globals::getFinalUploadDir();
    }

    protected function addFilter(&$view) {
        if (!isset($view->vars['attr']['filter'])) {
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

    public function getName()
    {
        return 'upload_type';
    }
}