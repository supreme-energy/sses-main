<?php 
	$seldbname = $_REQUEST['seldbname'];
    $fileContent = file_get_contents($_FILES['userfile']['tmp_name']);
	$lines = explode("\n", $fileContent); 
    $pre_truncation_lines = count($lines);
    if(!is_dir("import_files/$seldbname/")){
    	mkdir("import_files/$seldbname/");
    }
    $filehandle = fopen("import_files/$seldbname/".$_FILES['userfile']['name'], "w+");
    fwrite($filehandle,$fileContent);
    fclose($filehandle);
	$lines = array_slice($lines, 0, 500); //10 is how many lines you want to keep
	$text = implode("\n", $lines);
	if($pre_truncation_lines > 500){
		$text .="\n\n-------DATA-TRUNCATED-FOR-DISPLAY---------------";
	}
	echo $text; 
?>