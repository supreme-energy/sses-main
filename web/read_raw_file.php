<?php 
	$seldbname = $_REQUEST['seldbname'];
	$filename  = $_REQUEST['filename'];
    
	$filehandle = fopen("import_files/$seldbname/".$filename, "r");
	$fileContent= "";
	while(! feof($filehandle))
	{
		$fileContent.=fgets($filehandle);
	}	
	fclose($filehandle);
	
	$lines = explode("\n", $fileContent); 
    $pre_truncation_lines = count($lines);
    
	$lines = array_slice($lines, 0, 500); //10 is how many lines you want to keep
	$text = implode("\n", $lines);
	if($pre_truncation_lines > 500){
		$text .="\n\n-------DATA-TRUNCATED-FOR-DISPLAY---------------";
	}
	echo $text; 
?>