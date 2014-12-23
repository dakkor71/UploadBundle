<?php

namespace Juice\UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Juice\UploadBundle\Lib\Globals;

class DefaultController extends Controller
{
    private $_allowed_mime_types = array('image/jpeg', 'image/png');

    public function getAllowedMimeTypes()
    {
        return $this->_allowed_mime_types;
    }

    public function getTmpFileFolder()
    {
        return Globals::getTmpUploadDir() . '/';
    }

    /**
     * @Route("/upload_file" , name="_upload_file")
     */
    public function uploadAction()
    {
        $tmpFile = $_FILES['file']['tmp_name'];
        $originalFileName = $_FILES['file']['name'];
        $result = $this->addTmpFile($tmpFile, $originalFileName, $_POST, $_FILES);
        // create a JSON-response with a 200 status code
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/copy_remote_file" , name="_copy_remote_file")
     */
    public function copyRemoteAction()
    {
        $fileUrl = $_POST['fileUrl'];
        $filename = basename($fileUrl);

        $result = $this->addTmpFile($fileUrl, $filename, $_POST, $_FILES, true);
        // create a JSON-response with a 200 status code
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/crop_file" , name="_crop_file")
     */
    public function cropAction()
    {
        $result = $this->_cropImage($_POST['cordinates'], $_POST['file']);

        // create a JSON-response with a 200 status code
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    private function _cropImage($coordinates, $fileName)
    {

        //prepare coordinates
        if ($coordinates['x'] < 0) {
            $coordinates['x'] = 0;
        }

        if ($coordinates['y'] < 0) {
            $coordinates['y'] = 0;
        }

        $container = $this->container;

        # The controller service
        $imagemanagerResponse = $container->get('liip_imagine.controller');

        # The filter configuration service
        $filterConfiguration = $container->get('liip_imagine.filter.configuration');

        # Get the filter settings
        $configuration = $filterConfiguration->get('custom_crop');

        # Update filter settings
        $configuration['filters']['crop']['size'] = array($coordinates['w'], $coordinates['h']);
        $configuration['filters']['crop']['start'] = array($coordinates['x'], $coordinates['y']);
        $filterConfiguration->set('custom_crop', $configuration);

        # Apply the filter
        $imagemanagerResponse->filterAction($this->getRequest(), $this->getTmpFileFolder() . $fileName, 'custom_crop');

        # Move the img from temp
        rename('media/cache/custom_crop/' . $this->getTmpFileFolder() . $fileName, $this->getTmpFileFolder() . $fileName);

        return $result = array(
            'status' => 'success'
        );
    }

    public function addTmpFile($tmpFile, $originalFileName, array $post, array $files, $external = false)
    {
        try {

            if ($files['file']['error'] != 0) {
                Throw new \Exception($this->codeToMessage($files['file']['error']));
            }

            $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder();
            $fileInfo = pathinfo($originalFileName);

            //validate file MIME type
            $this->_checkMimeType($tmpFile);

            $tmpName = $this->_createTmpName($fileInfo['extension']);

            $targetFile = $targetPath . $tmpName;
            if ($external) {
                copy($tmpFile, $targetFile);
            } else {
                move_uploaded_file($tmpFile, $targetFile);
            }

            $result = array(
                'success' => true,
                'params' => array(
                    'fileName' => $tmpName,
                )
            );

            if (isset($post['kind']) && $post['kind'] == 'image') {
                list($width, $height) = getimagesize($targetFile);
                $result['params']['size'] = array(
                    'width' => $width,
                    'height' => $height
                );
            }

            // remove useless tmp files
            $this->clearFiles();

        } catch (\Exception $e) {
            $result = array('status' => 'error', 'error' => $e->getMessage());
        }

        return $result;
    }

    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }

    private function _createTmpName($extension)
    {
        $tmpName = hash('sha256', microtime() . rand()) . '.' . $extension;

        while (is_file($_SERVER['DOCUMENT_ROOT'] . $this->getTmpFileFolder() . $tmpName)) {
            $tmpName = hash('sha256', microtime() . rand()) . '.' . $extension;
        }

        return $tmpName;
    }

    public function removeTmpFiles(array $tmpFiles)
    {
        $em = $this->getDoctrine()->getManager();
        $tmpFileRepository = $em->getRepository('JuiceUploadBundle:TmpFile');

        foreach ($tmpFiles as $tmpFileId => $tmpFileName) {
            //remove tmp file from DB if exist 
            $tmpFile = $tmpFileRepository->find($tmpFileId);
            if (is_object($tmpFile)) {
                $em->remove($tmpFile);
            }

            //delete tmp file
            $this->_deleteTmpFile($tmpFileName);
        }
        $em->flush();
    }

    private function _deleteTmpFile($filepath)
    {
        if (is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder() . $filepath)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder() . $filepath);
        }
    }

    private function _checkMimeType($file)
    {
        //TODO - trzeba trzymac tutaj typy w tablicy i w zależności od rodzaju sprawdzac.
        //FIXME deprecated and shit!
//         if(!in_array(mime_content_type($file) , $this->getAllowedMimeTypes())) {
//             //throw new \Exception('invalid MIME type');
//         };
    }

    public function clearFiles()
    {
        $excludedFiles = array('.', '..', '.gitignore', '.gitkeep');
        $checkTime = time() - 60 * 60 * 10;
        if ($handle = opendir($_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder())) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry, $excludedFiles) && filemtime($_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder() . $entry) < $checkTime) {
                    $this->_deleteTmpFile($entry);
                }
            }

            closedir($handle);
        }
    }

    private function isImage($filename)
    {
        if (@is_array(getimagesize($filename))) {
            return true;
        } else {
            return false;
        }
    }

}
