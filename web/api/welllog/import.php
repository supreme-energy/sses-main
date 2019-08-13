<?php 
function load_import_result($status, $retstr){
   return array("status" => $status, "results" => $retstr);
}
require 'HTTP/Upload.php';
include ("../api_header.php");
$upload = new http_upload('en');
$file = $upload->getFiles('userfile');

if (PEAR::isError($file)) {
    echo json_encode(array("status" => "error", "message" => $file->getMessage()));
    exit;
}

if ($file->isValid()) {
    $file->setName('uniq');
    $dest_dir = '../../tmp/';
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

$filename="../../tmp/$seldbname.las";
$ifn=sprintf("$dest_dir%s", $file->getProp('name'));
if(file_exists($filename))	unlink($filename);
rename($ifn, $filename);
$retstr=array();
$retval=0;
exec("../../sses_laschk -f $filename -d $seldbname", &$retstr, &$retval);
if($retval!=0){
    echo json_encode(load_import_result('error', $retstr));
} else {
    echo json_encode(load_import_result('success', $retstr));
}
?>