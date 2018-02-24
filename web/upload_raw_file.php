<?php 
	$fileContent = file_get_contents($_FILES['userfile']['tmp_name']);
	echo $fileContent; 
?>