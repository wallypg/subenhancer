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
              echo add_jscript('jquery-3.1.1.min');
              echo '<style>.ocr-highlight{background-color: #ffff00}input[type="button"]{position:fixed;top:10px;right:10px;}</style>';
              echo '<input type="button" value="TOGGLE">';
              echo '<script>
                      $("input[type=\'button\']").click(function(){
                        $(\'div\').each(function(){
                          if($(this).find(\'.ocr-highlight\').length <= 0) $(this).toggle();
                        });
                      });
                    </script>';

              while (($line = fgets($handle)) !== false) {
                  $line = mb_convert_encoding($line, 'utf-8', "windows-1252");
                  if(!preg_match('/(\d{2}\:\d{2}\:\d{2}\,\d{3}\s-->\s\d{2}\:\d{2}\:\d{2}\,\d{3})|(^\s*\d+\s*$)|(^\s*$)/',$line)) {
                      $ocrResult = $this->_CI->ocr->ocrCheck($line,true);
                      
                      echo '<div><strong>Frase:</strong><br>'.$line.'<br>';
                      if(isset($ocrResult['ocredLine'])) {
                        echo '<br><br><strong>Resultado:</strong><br>'.$ocrResult['ocredLine'];
                      }
                      else echo '<strong>Sin optimización.</strong>';
                      echo '<br><br><strong>Info:</strong><br>';
                      echo '<pre>';
                      print_r($ocrResult);
                      echo '</pre>';
                      echo '<hr></div>';
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