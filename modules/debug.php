<?php 
require('modules/ocr.php');

if(isset($_POST['dbg'])) $method = 4;

if(isset($_GET['dbg'])) {
	if(isset($_GET['filename']) && !empty($_GET['filename'])) {
		$file = 'srt/original/'.$_GET['filename'].'.srt';
		if(file_exists(utf8_decode($file))) {
			$handle = fopen(utf8_decode($file), "r");
			if ($handle) {
			    echo '<style>.ocr-highlight{background-color: #ffff00}</style>';
			    while (($line = fgets($handle)) !== false) {
			        $line = mb_convert_encoding($line, 'utf-8', "windows-1252");
			    	if(!preg_match('/\d{2}\:\d{2}\:\d{2}\,\d{3}\s-->\s\d{2}\:\d{2}\:\d{2}\,\d{3}/',$line)) {
						echo 'Frase:<br>'.$line.'<br>';
						$ocrResult = ocrCheck($line);
						if(isset($ocrResult['ocredLine'])) echo '<br><br>Resultado:<br>'.$ocrResult['ocredLine'];
						else echo 'Sin optimización.';
						echo '<br><br>Array retornado:<br>';
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
	} elseif(isset($_GET['string']) && !empty($_GET['string'])) {
		echo '<style>.ocr-highlight{background-color: #ffff00}</style>';
		$string = $_GET['string'];
		echo 'Frase:<br>'.$string.'<br>';
		$ocrResult = ocrCheck($string);
		if(isset($ocrResult['ocredLine'])) echo '<br><br>Resultado:<br>'.$ocrResult['ocredLine'];
		else echo 'Sin optimización.';
		echo '<br><br>Array retornado:<br>';
		echo '<pre>';
		print_r($ocrResult);
		echo '</pre>';
		die();
	}
	// else die('Agregar el parámetro "string" para comprobar una frase o "filename" para escanear líneas de un archivo.');
}

?>