<?php
require 'HTTP/Upload.php';
include ("../api_header.php");
$upload = new http_upload('en');
$file = $upload->getFiles('file');
if (PEAR::isError($file)) {
    echo json_encode(array("status" => "error", "message" => $file->getMessage()));
    exit;
}
if ($file->isValid()) {
    $file->setName('uniq');
    $dest_dir = '/tmp/';
    $dest_name = $file->moveTo($dest_dir);
    if (PEAR::isError($dest_name)) {
        die ($dest_name->getMessage());
    }
    $real = $file->getProp('real');
} elseif ($file->isMissing()) {
    echo json_encode(array("status" => "error", "message" => "No file selected"));
    exit;
} elseif ($file->isError()) {
    echo json_encode(array("status" => "error", "message" => $file->errorMsg()));
    exit;
}

$filename="/tmp/".$prefix.$seldbname.".las";
$ifn=sprintf("$dest_dir%s", $file->getProp('name'));
if(file_exists($filename))	unlink($filename);
rename($ifn, $filename);
$infile=fopen("$filename", "r");
if(!$infile){
    echo json_encode(array("status" => "error", "message" => "file not found. Send again with file_check."));
    exit;
}
if($validate_las){
    //GAMA.API
    $found_gamma= false;
    $in_data = false;
    $in_headers = false;
    $line_num = 1;
    while (($line = fgets($infile)) !== false) {
        if(stristr($line, "~Curve")==true){
            $in_headers = true;
        }
        if($in_headers){
            if(stristr($line,'GAMA.API') || stristr($line,'GR_MWD.API')){
                $found_gamma==true;
            }
        }
        if(stristr($line, "~A")==true){
            $in_data=true;
            $in_headers=false;
        }
        if($in_data){
            $res = explode(' ', $line);
            foreach($res as $r){
                if(!is_numeric($r)){
                    fclose($infile);
                    echo json_ecode(array("status" => "error", "LAS File detected non numeric value at line ".$line_num));
                    exit;
                }
            }
        }
        $line_num++;
    }
    if(!$found_gamma){
        fclose($infile);
        echo json_ecode(array("status" => "error", "Gamma header not found in ~Curve, expected GAMA.API"));
        exit;
    }
}
if($validate_survey){    
    $line_num = 1;
    while (($line = fgets($infile)) !== false) {
        $res = explode(',', $line);        
        foreach($res as $r){
            if(!is_numeric($r)){
                fclose($infile);
                echo json_econde(array("status" => "error", "Survey File detected non numeric value at line ".$line_num));
                exit;
            }
        }
        $line_num++;
    }            
}
fclose($infile);
echo json_encode(array("status" => "success", "message" => "file uploaded"));
?>