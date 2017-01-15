<?
	require_once("dbio.class.php");
	$dbname = $_REQUEST['seldbname'];
	$report_id = $_REQUEST['id'];
	$db = new dbio($dbname);
	$db->OpenDb();
	$sql = "select * from reports where id = '$report_id'";
	$db->DoQuery($sql);
	$row = $db->FetchRow();
	$fn = $row['report_file'];
	if(isset($_REQUEST['png'])){
		$pdf_fn=$fn;
		$fn = str_replace('.pdf','.png',$fn);
		if(!file_exists($fn)){
			exec("convert -interlace none -density 300 -quality 100 $pdf_fn $fn");
		}
		header('Content-type:image/png');
		readfile("$fn");
	} else {
		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=\"".basename($fn)."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($fn));
		readfile("$fn");
	}
?>