<?php 
// error_reporting(E_ALL);
// if (!isset($submit)) { exit; }
require 'HTTP/Upload.php';
include ("../api_header.php");
include("../shared_functions/pterm_change.php");
include("../../readwellinfo.inc.php");
function StripExtraSpace($s) {
	$newstr="";
	$tok=strtok($s, " ,\t");
	while($tok!=false) {
		$newstr=$newstr."$tok";
		$tok=strtok(" ,\t");
		if($tok!=false) $newstr=$newstr." ";
	}
	return $newstr;
} 
function AddSurvey($db, $d, $i, $a) {
	$db2=new dbio($db);
	$db2->OpenDb();
	$db2->DoQuery("SELECT * FROM surveys WHERE md=$d;");
	if($db2->FetchRow()) {
		$id = $db2->FetchField("id");
		$db2->DoQuery("UPDATE surveys SET md=$d,inc=$i,azm=$a WHERE id=$id;");
		$db2->DoQuery("UPDATE wellinfo SET pamethod='-1';");
	}
	else {
		$db2->DoQuery("INSERT INTO surveys (md,inc,azm) VALUES ($d,$i,$a);");
	}
	$db2->CloseDb();
}

// Main Execution
$filename=$_REQUEST['filename'];
$seldbname=$_REQUEST['seldbname'];

$real=$_POST['real'];
if($filename==""){
    echo json_encode(array("status" => "error", "message" => "filename required."));
    exit;
}
if($pterm_method == 'bp'){
    ptermChange($pterm_method, $db);
}
// find the last depth of data already imported
$db->DoQuery("SELECT endmd,scalebias,scalefactor FROM welllogs ORDER BY endmd DESC LIMIT 1;");
$lastbias=0;
$lastscale=1.0;
$initialize_pas = false;
if($db->FetchRow()) {
	$lastendmd = $db->FetchField("endmd");
	$lastbias = $db->FetchField("scalebias");
	$lastscale = $db->FetchField("scalefactor");
}
if(!isset($lastendmd)){
    $initialize_pas = true;
}

// get the edata definitions
$etablenames=array();
$db->DoQuery("SELECT tablename FROM edatalogs ORDER BY colnum;");
while($db->FetchRow()) $etablenames[]=$db->FetchField("tablename");
$etablecount=count($etablenames);

// fetch the edata depth range
$eendmd=array();
for($i=0; $i<$etablecount; $i++) {
	$db->DoQuery("SELECT md FROM \"$etablenames[$i]\" ORDER BY md DESC LIMIT 1;");
	if($db->FetchRow()) $eendmd[$i]=$db->FetchField("md");
	else $eendmd[$i]=0;
}

// open up files for transfer
$infile=fopen("$filename", "r");
if(!$infile){
    echo json_encode(array("status" => "error", "message" => "file not found. Send again with file_check."));
    exit;
}
    
$tempfile=tmpfile();
$startmd=$startvs=$starttvd=99999;
$endmd=$endvs=$endtvd=-99999;

// check for valid data section
do {
	$line=fgets($infile,1024);
	if($line==FALSE){
	    echo json_encode(array("status" => "error", "message" => "End of file looking for ~A data section"));
	    exit;
	}		
} while(stristr($line, "~A")==FALSE);

// fetch the ascii log data section
while($line=fgets($infile,1024)) {
	$line=StripExtraSpace($line);
	$line=Trim($line);
	if(strlen($line)>1) {
		fputs($tempfile, $line);
		fputs($tempfile, "\n");
	}
}
fclose($infile);
unlink($filename);

// create an entry in the welllogs table
$result=$db->DoQuery("INSERT INTO welllogs (tablename) VALUES ('xxxxxx');");
if($result==FALSE){
    echo json_encode(array("status" => "error", "message" => "Database error attempting to insert a new welllog information block"));
    exit;    
}
// fetch its id and create table which contains imported data
$db->DoQuery("SELECT id,tablename FROM welllogs WHERE tablename='xxxxxx';");
if($db->FetchRow()) {
	$id=$db->FetchField("id");
	$tablename="wld_$id";
	$query="CREATE TABLE \"$tablename\" (id serial not null primary key, md float, tvd float, vs float, value float, hide smallint not null default 0, depth float not null default 0);";
	$result=$db->DoQuery($query);
	if($result!=FALSE) {
		$query="UPDATE welllogs SET tablename='$tablename',realname='$real' WHERE id='$id';";
		$result=$db->DoQuery($query);
	}
}
else {
    echo json_encode(array("status" => "error", "message" => "Id for new table entry not found!"));
    exit;  
}
if($result==FALSE) {
    if($id!="") {
        $db->DoQuery("DELETE FROM welllogs WHERE id='$id';");
    }
	$db->DoQuery("DROP TABLE IF EXISTS\"$tablename\";");
	echo json_encode(array("status" => "error", "message" => "Database error attempting to create table: $tablename"));
	exit;  	
}


// reset the file pointer and save data to table
$datacnt=0;
$gotsurvey=0;
$lastinc=$lastazm=-9999;
fseek($tempfile,0);
$db->DoQuery("BEGIN TRANSACTION;");
while (($data = fgetcsv($tempfile, 5000, " ")) !== FALSE) {
	$colcount=count($data);
	if($colcount<4)	break;
	$md=$data[0];
	$val=$data[1];
	$tvd=$data[2];
	$vs=$data[3];
	$inc=$data[4];
	$azm=$data[5];

	if($md>$lastendmd && $val>-999.0) {
		if($datacnt==0) {
			$startvs=$vs;
			$startmd=$md;
			$starttvd=$tvd;
		}
		$endmd=$md;
		$endvs=$vs;
		$endtvd=$tvd;
		$result=$db->DoQuery("INSERT INTO \"$tablename\" (md,value,tvd,vs,depth) VALUES ($md,$val,$tvd,$vs,$md);");
		if($result==FALSE) {
			$db->DoQuery("ROLLBACK;");
			echo json_encode(array("status" => "error", "message" => "Error updating table: $tablename"));
			exit;  
		}
		$datacnt++;
	}

	if($md>$lastendmd) {
		if($inc>0 && $azm>0) {
			if($inc!=$lastinc || $azm!=$lastazm) {
				AddSurvey($seldbname, $md, $inc, $azm);
				$lastinc=$inc;
				$lastazm=$azm;
				$gotsurvey++;
			}
		}
		// check for edata
		for($i=0; $i<$etablecount; $i++) {
			if($md>$eendmd[$i]) {
				$col=$i+6;
				if($colcount>$col) {
					$tn=$etablenames[$i];
					$edata=$data[$col];
					if(strlen($edata)>0)
						$db->DoQuery("INSERT INTO \"$tn\" (md,value,tvd,vs) VALUES ($md,$edata,$tvd,$vs);");
				}
			}
		}
	}

}
$result=$db->DoQuery("COMMIT;");

if($result==FALSE) {
    echo json_encode(array("status" => "error", "message" => "Bad bad errors on COMMIT: $tablename"));
    exit; 
}
fclose($tempfile);
if($datacnt<=0) {
	$db->DoQuery("DELETE FROM welllogs WHERE id=$id;");
	$db->DoQuery("DROP TABLE \"$tablename\";");
	$db->CloseDb();
	$tablename="";
	echo json_encode(array("status" => "error", "message" => "import error"));
	exit();
}
else {

	$db->DoQuery("BEGIN TRANSACTION;");
	$db->DoQuery("UPDATE welllogs SET startdepth='$starttvd',enddepth='$endtvd' WHERE id='$id';");
	$db->DoQuery("UPDATE welllogs SET startmd='$startmd',endmd='$endmd' WHERE id='$id';");
	$db->DoQuery("UPDATE welllogs SET startvs='$startvs',endvs='$endvs' WHERE id='$id';");
	$db->DoQuery("UPDATE welllogs SET starttvd='$starttvd',endtvd='$endtvd' WHERE id='$id';");
	$db->DoQuery("UPDATE welllogs SET scalebias='$lastbias',scalefactor='$lastscale' WHERE id='$id';");
	$db->DoQuery("UPDATE welllogs SET fault='0',dip='0' WHERE id='$id';");
	$db->DoQuery("UPDATE welllogs SET filter='0',scaleleft='0',scaleright='0' WHERE id='$id';");
	$db->DoQuery("delete from projections where ptype='sld'");
	$result=$db->DoQuery("COMMIT;");
	if($result==FALSE){
	    echo json_encode(array("status" => "error", "message" => "Bad bad errors on COMMIT: welllogs"));
	    exit();
	}

	// now that information is in the system check if dip is to be calculated

	$dip = '0';
	$manualdip = '';

	$sql = "select * from adm_config where cname = 'autoapply' limit 1";
	$db->DoQuery($sql);
	if($db->FetchRow())
	{
		$autoapply = $db->FetchField('cvalue');
		if($autoapply == 'Manual')
		{
			$sql = "select * from adm_config where cname = 'manualdip' limit 1";
			$db->DoQuery($sql);
			if($db->FetchRow())
			{
				$dip = $db->FetchField('cvalue');
			}
		}
		elseif($autoapply == 'Calculated')
		{
			require_once '../../GetCalculatedDip.php';
			GetCalculatedDip($db,$dip);
		}
	}

	if($dip != '0')
	{
		$db->DoQuery("UPDATE welllogs SET fault='0',dip='$dip' WHERE id='$id';");
	}
}
if($initialize_pas){
    include_once("../projection/initialize_pas.php");
    $db2=new dbio($seldbname);
    $db2->OpenDb();    
    initializeFirstTimePas($db, $db2);
    $db2->CloseDb();
}
$db->CloseDb();

exec("../../sses_cc -d $seldbname");
exec("../../sses_gva -d $seldbname");
exec("../../sses_cc -d $seldbname -p");
exec ("../../sses_af -d $seldbname");
echo json_encode(array("status" => "success", "message" => "log data imported succesfully"));
?>
