<?php 
require_once("dbio.class.php");
function get_field($field, $default){
	$val = isset($_REQUEST[$field]) ? $_REQUEST[$field] : $default;
	return $val ? $val : $default;
}
$seldbname=$_REQUEST['seldbname'];
$bitoffset=$_REQUEST['bitoffset'];
$projdip=get_field('projdip',0);
$propazm=get_field('propazm',0);
$surveyid =get_field('survey_id', 0);
$md =get_field('md', 0);
$inc =get_field('inc', 0);
$azm =get_field('azm', 0);
$tvd =get_field('tvd', 0);
$vs =get_field('vs', 0);
$ns =get_field('ns', 0);
$ew =get_field('ew', 0);
$cd =get_field('cd', 0);
$ca =get_field('ca', 0);

$db=new dbio($seldbname);
$db->OpenDb();
$query="UPDATE wellinfo SET bitoffset='$bitoffset', propazm='$propazm',projdip='$projdip';";
$db->DoQuery($query);
echo $query;
$query = "update surveys set md=$md, inc=$inc, azm=$azm, tvd=$tvd, vs = $vs, ns = $ns, ew=$ew , cd=$cd, ca=$ca where id = $surveyid";
$db->DoQuery($query);
echo $query;
$db->CloseDb();

exec ("./sses_af -d $seldbname");
header("Location: welllogfilesel.php?seldbname=$seldbname&fromtiein=true");
?>