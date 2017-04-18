<?
	require_once("dbio.class.php");
	require_once("classes/Reports.php");
	$uid = 1;
	$report_loader = new Reports($_REQUEST);
	$result = $report_loader->get_report($_REQUEST);
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$result['filename']);
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . strlen($result['content']));
	ob_clean();
	flush();
	echo $result['content'];
	flush();
?>