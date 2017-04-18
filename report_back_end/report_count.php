<?php
	require_once("dbio.class.php");
	include('session_handler.php');
	require_once("classes/Reports.php");
	$report_loader = new Reports($_REQUEST);
	echo json_encode($report_loader->report_list($_REQUEST,'get_count'));
?>
