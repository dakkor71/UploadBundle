<?php

namespace Juice\UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Juice\UploadBundle\Lib\Globals;

class DefaultController extends Controller
{   
    private $_allowed_mime_types = array('image/jpeg' , 'image/png');
    
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
        $result = $this->addTmpFile($_FILES , $_POST);
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
        $result = $this->_cropImage($_POST['cordinates'] , $_POST['file']);

        // create a JSON-response with a 200 status code
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }

	/**
	 * @Route("/download_remote_file" , name="_download_remote_file")
	 */
	public function downloadRemoteFileAction()
	{
		$orginalFileName = $_POST['file'];
		$fileInfo = pathinfo($orginalFileName);
		$tmpName = $this->_createTmpName($fileInfo['extension']);
		$targetPath = $_SERVER['DOCUMENT_ROOT'] . $this->getTmpFileFolder();

		if($this->isImage($orginalFileName)) {
			if(filter_var($orginalFileName , FILTER_VALIDATE_URL) !== FALSE) {
				$content = file_get_contents($orginalFileName);
				$fp = fopen($targetPath . $tmpName, 'w');
				fwrite($fp, $content);
				fclose($fp);
			}

			$result = array(
				'status' => 'success',
				'file' => $tmpName
			);
		} else {
			$result = array(
				'status' => 'error'
			);
		}

		// create a JSON-response with a 200 status code
		$response = new Response(json_encode($result));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

    private function _cropImage($cordinates , $fileName) {
        
        //prepare cordinates
        if($cordinates['x'] < 0) {
            $cordinates['x'] = 0;
        }
        
        if($cordinates['y'] < 0) {
            $cordinates['y'] = 0;
        }
        
        $container = $this->container;

        # The controller service
        $imagemanagerResponse = $container->get('liip_imagine.controller');

        # The filter configuration service
        $filterConfiguration = $container->get('liip_imagine.filter.configuration');

        # Get the filter settings
        $configuracion = $filterConfiguration->get('custom_crop');

        # Update filter settings
        $configuracion['filters']['crop']['size'] = array($cordinates['w'], $cordinates['h']);
        $configuracion['filters']['crop']['start'] = array($cordinates['x'], $cordinates['y']);
        $filterConfiguration->set('custom_crop', $configuracion);

        # Apply the filter
        $imagemanagerResponse->filterAction($this->getRequest() , $this->getTmpFileFolder() . $fileName , 'custom_crop');

        # Move the img from temp
        rename('media/cache/custom_crop/' . $this->getTmpFileFolder() . $fileName , $this->getTmpFileFolder() . $fileName);

        return $result = array(
            'status' => 'success'
        );
    }
    
    public function addTmpFile(array $files , array $post) 
    {
        try {
            $tmpFile = $files['file']['tmp_name'];
            $orginalFileName = $files['file']['name'];
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder();
            $fileInfo = pathinfo($orginalFileName);

            //validate file MIME type
            $this->_checkMimeType($tmpFile);
            
            $tmpName = $this->_createTmpName($fileInfo['extension']);

            $targetFile = $targetPath . $tmpName; 
            move_uploaded_file($tmpFile , $targetFile);
           
            $result = array(
                'success' => true,
                'params' => array(
                    'fileName' => $tmpName,
                ) 
            );
            
        if(isset($post['kind']) && $post['kind'] == 'image') {
                list($width , $height) = getimagesize($targetFile);
                $result['params']['size'] = array(
                    'width' => $width,
                    'height' => $height
                );
            }
            
            // remove usless tmp files
            $this->clearFiles();
            
        } catch (\Exception $e) {
            $result = array('status' => 'error' , 'error' => $e->getMessage());
        }
        
        return $result;
    }
    
    private function _createTmpName($extension) {
        $tmpName = hash('sha256' , microtime().rand()) . '.' . $extension;
        
        while(is_file($_SERVER['DOCUMENT_ROOT'] . $this->getTmpFileFolder() . $tmpName)) {
            $tmpName = hash('sha256' , microtime().rand()) . '.' . $extension;
        }
        
        return $tmpName;
    }
    
    public function removeTmpFiles(array $tmpFiles) 
    {
        $em = $this->getDoctrine()->getManager();
        $tmpFileRepository = $em->getRepository('JuiceUploadBundle:TmpFile');
        
        foreach($tmpFiles as $tmpFileId => $tmpFileName) {
            //remove tmp file from DB if exist 
            $tmpFile = $tmpFileRepository->find($tmpFileId);
            if(is_object($tmpFile)) {
                $em->remove($tmpFile);
            }
            
            //delete tmp file
            $this->_deleteTmpFile($tmpFileName);
        }
        $em->flush();
    }
    
    private function _deleteTmpFile($filepath) 
    {
        if(is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder() . $filepath)) {
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
        $excludedFiles = array('.' , '..' , '.gitignore', '.gitkeep');
        $checkTime = time() - 60 * 60 * 10;
        if ($handle = opendir($_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder())) {
            while (false !== ($entry = readdir($handle))) {
                if(!in_array($entry , $excludedFiles) && filemtime($_SERVER['DOCUMENT_ROOT'] . '/' . $this->getTmpFileFolder() . $entry) < $checkTime) {
                    $this->_deleteTmpFile($entry);
                }
            }

            closedir($handle);
        }
    }

	private function isImage($filename)
	{
		if(@is_array(getimagesize($filename))){
			return true;
		} else {
			return false;
		}
	}
    
}
