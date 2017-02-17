<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Dropzone extends CI_Controller {
  
    public function __construct() {
       parent::__construct();
       $this->load->helper(array('url','html','form'));
    }
     
    public function upload() {
        $this->load->helper('core');

        if (!empty($_FILES)) {
            // Chequear tamaño y tipo del archivo.

            $fileContent = file_get_contents($_FILES["file"]["tmp_name"]);

            $pattern = '/\d{1,4}\n\d{2}:\d{2}:\d{2},\d{3}\s-->\s\d{2}:\d{2}:\d{2},\d{3}\n.+\n(?:.+\n)?(?:.+\n)?(?:.+\n)?(?:.+\n)?(?:.+\n)?/';

            if(preg_match_all($pattern, $fileContent, $matches)){

                $formattedSubtitle = '';
                foreach($matches[0] as $match) {
                    $formattedSubtitle .= $match . "\n";
                }
                    

                $fileName = uniqid('subextract-');
                $targetPath = getcwd() . '/uploads/';
                $targetFile = $targetPath . $fileName . '.srt';
                file_put_contents($targetFile, $formattedSubtitle);
                echo $fileName;
                // $tempFile = $_FILES['file']['tmp_name'];
                // move_uploaded_file($tempFile, $targetFile);


            }

        }
    }

    public function download() {
        $getArray = $this->input->get();
        if( isset($getArray['file']) ) {
            $filename = $getArray['file'] . '.srt';
            $path = 'uploads/'.$filename;
            if( file_exists($path) ) {
                $fileContent = file_get_contents($path);
                $fileContent = utf8_decode($fileContent);
                
                unlink($path);
                header("Content-Type: text/plain;charset=windows-1252");
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                header("Content-Length: " . strlen($fileContent));
                echo $fileContent;
                die();
            }
        }
        header('Location:'.base_url());
        die();
    }
}