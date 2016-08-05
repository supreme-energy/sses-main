<?
	session_start();
	require_once("dbio.class.php");
	require_once("classes/Reports.php");
	if(!$reports_loader){
		$report_loader = new Reports($_REQUEST);
	}
	
	$data = $report_loader->report_list($_REQUEST);
	$new = isset($_REQUEST['new'])?"&new=1":"";
	$link='index.php?cmd=report_view&ja='.$_REQUEST['ja'];
	foreach($data->results as $r){
		$link="index.php?cmd=report_view&ja=".$_REQUEST['ja']."&id=".$r->id.$new;
		break;
	}
	
	header("Location: $link");
	
?>