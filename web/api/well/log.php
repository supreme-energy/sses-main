 <?php
	include ("../api_header.php");
	$tablename = $_REQUEST ['tablename'];
	$results = array ();
	if (isset ( $tablename ) && ! empty ( $tablename )) {
		$db->DoQuery ( "SELECT * FROM welllogs where tablename='$tablename'" );
		$db->FetchRow ();
		$results = array (
				"id" => $id,
				"tablename" => $tablename,
				"realname" => $db->FetchField ( 'realname' ),
				"startmd" => $db->FetchField ( 'startmd' ),
				"endmd" => $db->FetchField ( 'endmd' ),
				"starttvd" => $db->FetchField ( 'starttvd' ),
				"endtvd" => $db->FetchField ( 'endtvd' ),
				"startvs" => $db->FetchField ( 'startvs' ),
				"endvs" => $db->FetchField ( 'endvs' ),
				"startdepth" => $db->FetchField ( 'startdepth' ),
				"enddepth" => $db->FetchField ( 'enddepth' ),
				"fault" => $db->FetchField ( 'fault' ),
				"sectdip" => $db->FetchField ( 'dip' ),
				"secttot" => $db->FetchField ( 'tot' ),
				"sectbot" => $db->FetchField ( 'bot' ),
				"scalebias" => $db->FetchField ( 'scalebias' ),
				"scalefactor" => $db->FetchField ( 'scalefactor' ) 
		);
		include ("read_welllog_include.php");
		$results ['data'] = $data;
	} else {
		$results = array (
				"status" => "Failed",
				"message" => "missing required parameter tablename" 
		);
	}
	echo json_encode ( $results );
	?>