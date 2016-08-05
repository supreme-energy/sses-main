<?
	require_once("dbio.class.php");
	$md_on = isset($_REQUEST['md'])?'1':'0';
	$inc_on= isset($_REQUEST['inc'])?'1':'0';
	$azm_on = isset($_REQUEST['azm'])?'1':'0';
	$avg_dip = isset($_REQUEST['avgd'])?'1':'0';
	$avg_gas = isset($_REQUEST['avgg'])?'1':'0';
	$avg_rop = isset($_REQUEST['avgr'])?'1':'0';
	$footage = isset($_REQUEST['footage'])?'1':'0';
	$comment = isset($_REQUEST['comment'])?'1':'0';
	$in_zone = isset($_REQUEST['inzn'])?'1':'0';
	$query = "update appinfo set anno_settings='md:$md_on|inc:$inc_on|azm:$azm_on|avg_dip:$avg_dip|avg_gas:$avg_gas|avg_rop:$avg_rop|footage:$footage|in_zone:$in_zone|comment:$comment';";
	$db3=new dbio($_REQUEST['seldbname']);
	$db3->OpenDb();
	$db3->DoQuery($query);
	header('Location: annotations.php?seldbname='.$_REQUEST['seldbname']);

?>
