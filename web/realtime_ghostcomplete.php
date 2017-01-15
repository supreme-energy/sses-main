<?php
/*
 * Created on Aug 5, 2016
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("dbio.class.php");
if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_POST['seldbname']) ? $_POST['seldbname'] : '');
$md=(isset($_POST['md'])?$_POST['md']:0);
$inc=(isset($_POST['inc'])?$_POST['inc']:0);
$azm=(isset($_POST['azm'])?$_POST['azm']:0);

$db= new dbio($seldbname);
$db->OpenDb();
$sql = "update surveys set isghost=0 where isghost=1;";
$db->DoQuery($sql);
$sql = "update welllogs set isghost=0 where isghost=;1";
$db->DoQuery($sql);
$sql = "update appinfo set ghost_dip=0,ghost_fault=0;";
$db->DoQuery($sql);
$sql ="update wellinfo set rt_stream_ghost=0;";
$db->DoQuery($sql);
$sql = "delete from ghost_data;";
$db->DoQuery($sql);
$db->CloseDb();
?>
<!doctype html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="gva_tab8.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<title>SGTA Editor<?php echo " ($seldbname)"; ?></title>
<style>
#clickimage[src=''] {
	display:none;
}
</style>
<script src='//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js'></script>
<script>
function reloadAndClose(){
	if(window.opener && !window.opener.closed){
//				window.opener.callMask(true);
				window.opener.location.reload()
	}
	window.location="/sses/realtime.php?seldbname=<?=$seldbname?>";
}	
setTimeout(reloadAndClose, 100);
</script>
</head>
</html>
