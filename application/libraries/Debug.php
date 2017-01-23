<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Debug {

  public function __construct()
  {
      $this->_CI =& get_instance();
      $this->_CI->load->library("Ocr");
  }

  public function ocrDebugger($getArray){
    if( isset($getArray['filename']) && !empty($getArray['filename']) ) {

      $file = 'srt/original/'.$getArray['filename'].'.srt';
      if(file_exists(utf8_decode($file))) {
          $handle = fopen(utf8_decode($file), "r");
          if ($handle) {
              echo '<style>.ocr-highlight{background-color: #ffff00}</style>';
              while (($line = fgets($handle)) !== false) {
                  $line = mb_convert_encoding($line, 'utf-8', "windows-1252");
                  if(!preg_match('/\d{2}\:\d{2}\:\d{2}\,\d{3}\s-->\s\d{2}\:\d{2}\:\d{2}\,\d{3}/',$line)) {
                      echo '<strong>Frase:</strong><br>'.$line.'<br>';
                      $ocrResult = $this->_CI->ocr->ocrCheck($line,true);
                      if(isset($ocrResult['ocredLine'])) echo '<br><br><strong>Resultado:</strong><br>'.$ocrResult['ocredLine'];
                      else echo '<strong>Sin optimización.</strong>';
                      echo '<br><br><strong>Info:</strong><br>';
                      echo '<pre>';
                      print_r($ocrResult);
                      echo '</pre>';
                      echo '<hr>';
                  }
              }
              fclose($handle);
          } else die('Error abriendo el archivo');
      } else die('Archivo no encontrado.');
      die();

    } elseif( isset($getArray['string']) && !empty($getArray['string']) ) {

      echo '<style>.ocr-highlight{background-color: #ffff00}</style>';
      $string = $getArray['string'];
      echo '<strong>Frase:</strong><br>'.$string.'<br>';
      $ocrResult = $this->_CI->ocr->ocrCheck($string,true);
      if(isset($ocrResult['ocredLine'])) echo '<br><strong>Resultado:</strong><br>'.$ocrResult['ocredLine'];
      else echo '<strong>Sin optimización.</strong>';
      echo '<br><br><strong>Info:</strong><br>';
      echo '<pre>';
      print_r($ocrResult);
      echo '</pre>';
      die();

    }    
  }
}

?>