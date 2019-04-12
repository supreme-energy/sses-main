 <?php
	include ("api_header.php");
	$db->DoQuery ( "SELECT * FROM welllogs order by startmd asc" );
	$with_data = $_REQUEST ['data'] == '1';
	$results = array();
	while ( $db->FetchRow () ) {
		$id = $db->FetchField ( "id" );
		$tablename = $db->FetchField ( "tablename" );
		$result = array (
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
		if ($with_data) {
			include ("read_welllog_include.php");
			$result ['data'] = $data;
		}
		array_push ( $results, $result );
	}
	
	echo json_encode ( $results );
	?>