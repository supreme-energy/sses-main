<?php 
$dir = "import_files/$seldbname/";

// Open a directory, and read its contents
$import_files = Array();
if (is_dir($dir)){
	if ($dh = opendir($dir)){
		while (($file = readdir($dh)) !== false){
			if($file == '.' || $file == '..') continue;			
			array_push($import_files, $file);
		}
		closedir($dh);
	}
}
?>