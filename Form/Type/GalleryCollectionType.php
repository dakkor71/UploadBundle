<?php

namespace Juice\UploadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class GalleryCollectionType extends AbstractType
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
        return 'collection';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!isset($view->vars['attr']['filter'])) {
            throw new NotFoundHttpException("You need to define filter name");
        }

        $view->vars['button_label'] = $options['button_label'];
        $view->vars['upload_class'] = $options['upload_class'];
        $view->vars['multi'] = true;

        $filter = $view->vars['attr']['filter'];
        $config = $this->filterConfiguration->get($filter);
        list($width , $heigth) = $config['filters']['thumbnail']['size'];
        $ratio = $width / $heigth;
        $minSize = '({width : ' . $width . ',height : ' . $heigth . '})';

        $view->vars['attr']['data-ratio'] = $ratio;
        $view->vars['attr']['data-minSize'] = $minSize;
        $view->vars['attr']['data-minSize'] = $minSize;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'upload_class' => 'juice_upload',
            'button_label' => 'Upload',
        ));
    }

    public function getName()
    {
        return 'juice_gallery_collection';
    }
}