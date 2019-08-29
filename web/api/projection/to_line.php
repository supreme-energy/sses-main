<?
include("../api_header.php");
include("./functions.php");
$seldbname=$_REQUEST['seldbname'];
$ret=$_REQUEST['ret'];
$method=3;
$data=$_REQUEST['data'];
$project=$_REQUEST['project'];
$bitoffset=$_REQUEST['bitoffset'];
$md=$_REQUEST['md'];
$inc=$_REQUEST['inc'];
$azm=$_REQUEST['azm'];
$tvd=$_REQUEST['tvd'];
$vs=$_REQUEST['vs'];
$ca=$_REQUEST['ca'];
$cd=$_REQUEST['cd'];
$tpos=$_REQUEST['tpos'];
$tot=$_REQUEST['tot'];
$bot=$_REQUEST['bot'];
$dip=$_REQUEST['dip'];
$fault=$_REQUEST['fault'];
// $fault=0;
$pmd=$_REQUEST['pmd'];
$pinc=$_REQUEST['pinc'];
$tinc=$_REQUEST['tinc'];
$tazm=$_REQUEST['tazm'];
$pazm=$_REQUEST['pazm'];
$ptvd=$_REQUEST['ptvd'];
$pca=$_REQUEST['pca'];
$pcd=$_REQUEST['pcd'];
$currid=$_REQUEST['currid'];
$newid=$_REQUEST['newid'];
$tf = $_REQUEST['tf'];
$skiprot = $_REQUEST['skiprot']=='true'?true:false;
$delpa = explode(',',$_REQUEST['pavsdel']);
$motoryield = $_REQUEST['motoryield'];

$dmd=$md-$pmd;
$dinc=$inc-$pinc;
$dazm=$azm-$pazm;
$dtvd=$tvd-$ptvd;
$dcd=$cd-$pcd;
$dca=$ca-$pca;


$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("Update projections set inc=$tinc,azm=$tazm where ptype='rot'");
if($motoryield){
	$db->DoQuery("Update wellinfo set motoryield=$motoryield");
}
if($skiprot){
	$query = "delete from projections where ptype='rot';";
	$db->DoQuery($query);
}
foreach($delpa as $paid){
	if($paid){
		$query = "delete from projections where id=$paid";
		$db->DoQuery($query);
	}
}
if($project!='ahead') {
	$plan = 1;
	$db->DoQuery("UPDATE wellinfo SET pbmethod=$method");
	$db->DoQuery("UPDATE wellinfo SET projdip='$dip';");
	$db->DoQuery("UPDATE wellinfo SET bitoffset='$bitoffset';");
	if($method==0) $db->DoQuery("UPDATE wellinfo SET pbdata='$dmd,0,0';");
	else if($method==1) $db->DoQuery("UPDATE wellinfo SET pbdata='$dtvd,$dcd,$dca';");
	else if($method==2) $db->DoQuery("UPDATE wellinfo SET pbdata='$dtvd,$dcd,$dca';");
	else if($method>=3 && $method<=5) $db->DoQuery("UPDATE wellinfo SET pbdata='$dmd,$dinc,$dazm';");
	else $db->DoQuery("UPDATE wellinfo SET pbdata='0,0,0';");
} else {
	if($currid!="") {
		if($method==0) $data="$dmd,0,0";
		else if($method>=3 && $method<=5) $data="$dmd,$dinc,$dazm";
		else if($method==6) $data="$tvd,$vs,$tpos";
		else if($method==7) $data="$tot,$vs,$tpos";
		else if($method==8) $data="$vs,$tpos,$dip,$fault";
		else $data="0,0,0";
		$db->DoQuery("UPDATE projections SET method='$method',data='$data',
md='$md',inc='$inc',azm='$azm',dip='$dip',fault='$fault',tot='$tot',bot='$bot',tvd='$tvd',vs='$vs' WHERE id=$currid;");
	} else {
		if($method==0) $data="$dmd,0,0";
		else if($method>=3 && $method<=5) $data="$dmd,$dinc,$dazm";
		else if($method==6) $data="$tvd,$vs,$tpos";
		else if($method==7) $data="$tot,$vs,$tpos";
		else if($method==8) $data="$vs,$tpos,$dip,$fault";
		else $data="0,0,0";
		$db->DoQuery("INSERT INTO projections (method, data, md, inc, azm, dip, fault, tvd, vs, tot,tf,ptype) 
		VALUES ('$method','$data','$md','$inc','$azm','$dip','$fault','$tvd','$vs','$tot','$tf','sld');");
		$db->DoQuery("SELECT id FROM projections WHERE md=$md;");
		if($db->FetchRow()) $newid=$db->FetchField("id");
		
	}
}

exec("./sses_gva -d $seldbname");
exec("./sses_cc -d $seldbname");
exec("./sses_cc -d $seldbname -p");
exec("./sses_af -d $seldbname");

echo jsonListProjections();
?>
	

