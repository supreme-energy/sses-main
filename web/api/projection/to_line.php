<?
include("../api_header.php");
include("./functions.php");
$body = file_get_contents('php://input');
$json_body = json_decode($body);
$seldbname=$_REQUEST['seldbname'];

$method=3;
$data=$json_body->data;
$project=$json_body->project;
$bitoffset=$json_body->bitoffset;
$md=$json_body->md;
$inc=$json_body->inc;
$azm=$json_body->azm;
$tvd=$json_body->tvd;
$vs=$json_body->vs;
$ca=$json_body->ca;
$cd=$json_body->cd;
$tpos=$json_body->tpos;
$tot=$json_body->tot;
$bot=$json_body->bot;
$dip=$json_body->dip;
$fault=$json_body->fault;
// $fault=0;
$pmd=$json_body->pmd;
$pinc=$json_body->pinc;
$tinc=$json_body->tinc;
$tazm=$json_body->tazm;
$pazm=$json_body->pazm;
$ptvd=$json_body->ptvd;
$pca=$json_body->pca;
$pcd=$json_body->pcd;
$currid=$json_body->currid;
$newid=$json_body->newid;
$tf = $json_body->tf;
$skiprot = $json_body->skiprot =='true'?true:false;
$delpa = explode(',', $json_body->pavsdel);
$motoryield = $json_body->motoryield;

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
	

