<?php

namespace Juice\UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


use Juice\UploadBundle\Handler\ImageUploadHandler;
use Juice\UploadBundle\Handler\FileUploadHandler;
use Juice\UploadBundle\Handler\RemoteUploadHandler;

/**
 * @Route("/jub")
 */
class UploadController extends Controller
{

    /**
     * @Route("/upload_file", name="jub_upload_file")
     */
    public function uploadAction()
    {
        $tmpFile = $_FILES['file']['tmp_name'];
        $originalFileName = $_FILES['file']['name'];

        switch ($_POST['kind']) {
            case 'image':
                $uploadHandler = new ImageUploadHandler($tmpFile, $originalFileName, $_FILES, $_POST);
                break;
            case 'file':
                $uploadHandler = new FileUploadHandler($tmpFile, $originalFileName, $_FILES, $_POST);
                break;
        }

        try {
            $result = $uploadHandler->addTmpFile();
        } catch (\Exception $e) {
            $result = array('status' => 'error', 'error' => $e->getMessage());
        }

        return $this->generateResponse($result);
    }

    /**
     * @Route("/copy_remote_file", name="jub_copy_remote_file")
     */
    public function copyRemoteAction()
    {
        $fileUrl = $_POST['fileUrl'];
        $filename = basename($fileUrl);

        $uploadHandler = new ImageUploadHandler($fileUrl, $filename, $_FILES, $_POST);

        try {
            $result = $uploadHandler->addTmpFile();
        } catch (\Exception $e) {
            $result = array('status' => 'error', 'error' => $e->getMessage());
        }

        return $this->generateResponse($result);
    }

    /**
     * @Route("/crop_file", name="jub_crop_file")
     */
    public function cropAction()
    {
        try {
            $cropHandler = $this->get('juice_upload_bundle.handler.crop');
            $result = $cropHandler->cropImage($_POST['cordinates'], $_POST['file']);
        } catch (\Exception $e) {
            $result = array('status' => 'error', 'error' => $e->getMessage());
        }


        return $this->generateResponse($result);
    }

    private function generateResponse($result) {
        // create a JSON-response with a 200 status code
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
