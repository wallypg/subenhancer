<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"><!-- Your HTML file can still use UTF-8-->
<title>Untitled Document</title>
</head>
<body>
<?php
header('Content-Type: text/html; charset=utf-8');
$uploadOk = 1;

// Check file size
if ($_FILES["uploadedFile"]["size"] > 300000) {
    echo "Sorry, your file is too large.<br />";
    $uploadOk = 0;
}

// Allow certain file formats
$fileType = pathinfo($_FILES['uploadedFile']['name'],PATHINFO_EXTENSION);
if($fileType != "srt" ) {
    echo "Sorry, only SRT files are allowed.<br />";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) exit();

$fileContent = file_get_contents($_FILES['uploadedFile']['tmp_name']);
echo nl2br($fileContent);

// $completeFileArray = array();
// $timesArray = array();

// foreach(preg_split("/((\r?\n)|(\r\n?))/", $fileContent) as $key => $line){
//     // echo nl2br($line);
//     if(preg_match('/\d{2}:\d{2}:\d{2},\d{3} --> \d{2}:\d{2}:\d{2},\d{3}/',$line)) $timesArray = $line;
// 	$completeFileArray [$key] = $line;
// }
	// echo $line;
// echo $completeFileArray);

?>
</body>
</html>