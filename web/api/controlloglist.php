 <?php
	include ("api_header.php");
	$db->DoQuery ( "SELECT * FROM controllogs order by startmd asc" );
	$with_data = $_REQUEST ['data'] == '1';
	$results = array();
	while ( $db->FetchRow () ) {
		$id = $db->FetchField ( "id" );
		$tablename = $db->FetchField ( "tablename" );
		$result = array (
		    "id" => $id,
		    "tablename" => $tablename,
		    "realname" =>  $db->FetchField('realname'),
		    "startmd" => $db->FetchField('startmd'),
		    "endmd" => $db->FetchField('endmd'),
		    "tot" => $db->FetchField("tot"),
		    "bot" => $db->FetchField("bot"),
		    "dip" => $db->FetchField("dip"),
		    "azm" => $db->FetchField("azm")
		);
		if ($with_data) {
			include ("read_controllog_include.php");
			$result ['data'] = $data;
		}
		array_push ( $results, $result );
	}
	
	echo json_encode ( $results );
	?>