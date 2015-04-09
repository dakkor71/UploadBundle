<?php

namespace Juice\UploadBundle\Handler;

use Symfony\Component\DependencyInjection\Container;
use Juice\UploadBundle\Lib\Globals;

class CropHandler
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function cropImage($request, $coordinates, $fileName)
    {
        //prepare coordinates
        if ($coordinates['x'] < 0) {
            $coordinates['x'] = 0;
        }

        if ($coordinates['y'] < 0) {
            $coordinates['y'] = 0;
        }

        # The controller service
        $imagemanagerResponse = $this->container->get('liip_imagine.controller');

        # The filter configuration service
        $filterConfiguration = $this->container->get('liip_imagine.filter.configuration');

        # Get the filter settings
        $configuration = $filterConfiguration->get('custom_crop');

        # Update filter settings
        $configuration['filters']['crop']['size'] = array($coordinates['w'], $coordinates['h']);
        $configuration['filters']['crop']['start'] = array($coordinates['x'], $coordinates['y']);
        $filterConfiguration->set('custom_crop', $configuration);

        # Apply the filter
        $imagemanagerResponse->filterAction($request, Globals::getTmpUploadDir() . '/' . $fileName, 'custom_crop');

        # Move the img from temp
        rename('media/cache/custom_crop/' . Globals::getTmpUploadDir() . '/' . $fileName, Globals::getTmpUploadDir() . '/' . $fileName);

        return $result = array(
            'status' => 'success'
        );
    }
}
