<?php

namespace Juice\UploadBundle\Twig;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class FileExtension extends \Twig_Extension
{
    private $filterManager;

    public function __construct(FilterManager $filterManager) {
        $this->filterManager = $filterManager;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('checkFile', array($this, 'fileFilter')),
            new \Twig_SimpleFilter('getImageDimensions', array($this, 'getImageDimensions', array('is_safe' => array('html')))),
        );
    }

    public function fileFilter($path)
    {
        return file_exists($path);
    }

    public function getImageDimensions($filter)
    {
        $f = $this->filterManager->getFilterConfiguration()->get($filter);

        $result = 'data-width="' . $f['filters']['thumbnail']['size'][0] . '" data-height="' . $f['filters']['thumbnail']['size'][1] . '"';
        return $result;
    }

    public function getName()
    {
        return 'nhb_file_extension';
    }
}

?>