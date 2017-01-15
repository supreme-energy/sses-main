<?
	require_once("dbio.class.php");
	$startld = isset($_REQUEST['start_label_depth'])?$_REQUEST['start_label_depth']:0;
	$lblevr = isset($_REQUEST['label_every_cnt'])?$_REQUEST['label_every_cnt']:1;
	$disp_md = isset($_REQUEST['md'])?'1':'0';
	$disp_vs = isset($_REQUEST['vs'])?'1':'0';
	$disp_ornt = isset($_REQUEST['label_orientation'])?$_REQUEST['label_orientation']:0;
	$rep_inc = isset($_REQUEST['rep_inc'])?'1':'0';
	$wbplt_inc = isset($_REQUEST['wbplt_inc'])?'1':'0';
	
	$query = "update appinfo set labeling_start=$startld,label_every=$lblevr,label_dmd=$disp_md,label_dvs=$disp_vs,label_orient=$disp_ornt,label_dreport=$rep_inc,label_dwebplot=$wbplt_inc";	
	echo $query;
	$db3=new dbio($_REQUEST['seldbname']);
	$db3->OpenDb();
	$db3->DoQuery($query);
	$db3->CloseDb();
	header('Location: annotations.php?showing=1&seldbname='.$_REQUEST['seldbname']);

?>
