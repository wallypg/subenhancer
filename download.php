<?php 
if(isset($_GET['file']) && isset($_GET['name'])) {
	$filename = $_GET['name'];
	$path = 'srt/enhanced/'.$_GET['file'].'.srt';
	if($filename != '' && file_exists($path)) {
		$fileContent = file_get_contents($path);
		unlink($path);
		header("Content-Type: text/plain;charset=windows-1252");
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header("Content-Length: " . strlen($fileContent));
		echo $fileContent;
		die();
	}
}
header('Location:index.php');
die();
?>