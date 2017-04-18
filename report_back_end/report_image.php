<?
	require_once("dbio.class.php");
	require_once("classes/Reports.php");
	$uid = 1;
	$report_loader = new Reports($_REQUEST);
	$result = $report_loader->get_report_png($_REQUEST);
	header('Content-type:image/png');
	ob_clean();
	flush();
	echo $result['content'];
	flush();
?>