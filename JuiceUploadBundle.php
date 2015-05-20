<?php

namespace Juice\UploadBundle;

use Juice\UploadBundle\Lib\Globals;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JuiceUploadBundle extends Bundle
{
    public function boot()
    {
        // Set some static globals
        Globals::setFinalUploadDir($this->container->getParameter('final_upload_dir'));
        Globals::setTmpUploadDir($this->container->getParameter('tmp_upload_dir'));
        Globals::setAbsolutePath($this->container->getParameter('absolute_path'));
    }
}
