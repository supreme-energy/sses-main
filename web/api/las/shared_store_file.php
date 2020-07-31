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


$ifn=sprintf("$dest_dir%s", $file->getProp('name'));
if(file_exists($filename))	unlink($filename);
rename($ifn, $filename);
$infile=fopen("$filename", "r");
if(!$infile){
    echo json_encode(array("status" => "error", "message" => "file not found. Send again with file_check."));
    exit;
}
echo json_encode(array("status" => "success", "message" => "file uploaded"));
?>