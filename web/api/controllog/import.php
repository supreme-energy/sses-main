
<?php
include("../api_header.php");
require 'HTTP/Upload.php';
// print_r($HTTP_POST_FILES);
$upload = new http_upload('en');
$file = $upload->getFiles('userfile');
if (PEAR::isError($file)) {
	die ($file->getMessage());
}
if ($file->isValid()) {
	$file->setName('uniq');
	$dest_dir = '../../tmp/';
	$dest_name = $file->moveTo($dest_dir);
	if (PEAR::isError($dest_name)) {
		die ($dest_name->getMessage());
	}
} elseif ($file->isMissing()) {
    echo json_encode(array('status'=>'error', 'message'=>'file missing'));
    exit;
} elseif ($file->isError()) {
    echo json_encode(array('status'=>'error', 'message'=>$file->errorMsg()));
    exit;
}
$filename=sprintf("$dest_dir%s", $file->getProp('name'));


// drop all control log tables and remove their entries in controllogs table
$tablelist=array();
$tableids=array();
$db->DoQuery("SELECT * FROM controllogs;");
while($db->FetchRow()) {
	$id = $db->FetchField("id");
	$tn = $db->FetchField("tablename");
	$tablelist[]=$tn;
	$tableids[]=$id;
}
foreach($tablelist as $tn)
	$db->DoQuery("DROP TABLE \"$tn\";");
foreach($tableids as $id)
	$db->DoQuery("DELETE FROM controllogs WHERE id='$id';");

// create an entry in the controllogs table
$db->DoQuery("INSERT INTO controllogs (tablename) VALUES ('xxxxxx');");
$db->DoQuery("SELECT * FROM controllogs WHERE tablename='xxxxxx';");
$id="";
if($db->FetchRow())
	$id = $db->FetchField("id");

// create table which contains imported data
if($id!="") {
	$tablename="cld_$id";
	$query="CREATE TABLE \"$tablename\" (id serial not null primary key, md float, tvd float, vs float, value float, hide smallint not null default 0);";
	$db->DoQuery($query);
	$query="UPDATE controllogs SET tablename='$tablename' WHERE id='$id';";
	$db->DoQuery($query);
}
else {
    echo json_encode(array('status'=>'error', 'message'=>'Id for new table entry not found!'));
    exit;    
}

$startmd=$startvs=$starttvd=99999;
$endmd=$endvs=$endtvd=-99999;
if($tablename==""){
    echo json_encode(array('status'=>'error', 'message'=>"Table name not given $tablename"));
    exit;
}
if($filename==""){
    echo json_encode(array('status'=>'error', 'message'=>'LAS file name not given'));
    exit;    
}

$temp=tmpfile();
$infile=fopen("$filename", "r");
if(!$infile)	{
    echo json_encode(array('status'=>'error', 'message'=>"file not found: $filename"));
    exit;  
}

do {
	$line=fgets($infile,1024);
	if($line==FALSE){		
		echo json_encode(array('status'=>'error', 'message'=>"End of file looking for ~A data section"));
		exit; 
	}
} while(stristr($line, "~A")==FALSE);

while($line=fgets($infile,1024)) {
	$line=trim($line);
	$line=preg_replace( '/\s+/', ',', $line );
	fputs($temp, "$line\n");
}
fclose($infile);
fseek($temp,0);
$result=$db->DoQuery("DELETE FROM \"$tablename\";");
if($result==FALSE){
    echo json_encode(array('status'=>'error', 'message'=>"Table does not exist: $tablename -$result"));
    exit;     
}
	

fseek($temp,0);
$db->DoQuery("BEGIN TRANSACTION;");
while (($data = fgetcsv($temp, 5000, ",")) !== FALSE) {
	$md=$data[0];
	$val=$data[1];
	$tvd=$data[2];
	$vs=$data[3];
	if($md=="")	$md=0;
	if($tvd=="")	$tvd=0;
	if($vs=="")	$vs=0;
	if($value=="")	$value=0;
	$result=$db->DoQuery("INSERT INTO \"$tablename\" (md,value,tvd,vs) VALUES ($md,$val,$tvd,$vs);");
	if($result==FALSE){
	    echo json_encode(array('status'=>'error', 'message'=>"Table does not exist: $tablename -$result"));
	    exit; 
	}
	if($md>$endmd)	$endmd=$md;
	if($md<$startmd)	$startmd=$md;
}
$result=$db->DoQuery("COMMIT;");
if($result==FALSE){
    echo json_encode(array('status'=>'error', 'message'=>"Bad bad errors on COMMIT: $tablename"));
    exit;     
}
fclose($temp);
unlink($filename);
$db->DoQuery("UPDATE controllogs SET startmd=$startmd,endmd=$endmd WHERE id=$id;");
$db->CloseDb();
header("Location: ../controllog.php?seldbname=$seldbname&id=$id");