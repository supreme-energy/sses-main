<?
include("api_header.php");

function ptermChange($pterm_val, $db){
    if($pterm_val=='bp'){
        $query = "select * from projections";
        $db->DoQuery($query);
        $queries = array();
        while($db->FetchRow()){
            $id = $db->FetchField('id');
            $tvd = $db->FetchField('tvd');
            $vs = $db->FetchField('vs');
            $tot = $db->FetchField('tot');
            $tpos = $tot - $tvd;
            $data="$tvd,$vs,$tpos";
            array_push($queries, "update projections set method=6, data='$data' where id=$id");
        }
        foreach($queries as $query){
            $db->DoQuery($query);
        }
    }
}

function mapToDbTable($tablein){
	if($tablein == 'autorc'){
		return 'rigminder_connection';
	}
	return $tablein;
}

$response = array();
if($seldbname){
	$allowed_tables=array("wellinfo","appinfo", "autorc", "emailinfo", "witsml_details");
	
	if(strpos($_SERVER['CONTENT_TYPE'],'application/json') !== false){
	    $json_body = file_get_contents('php://input');
	    $obj = json_decode($json_body);
	    foreach($obj as $key => $value){
	        if(in_array($key, $allowed_tables)){
	            $table = mapToDbTable($key);
	            foreach($value as $field => $value){
	                $query = "update $table set $field = '$value'";	                
	                $result = $db->DoQuery($query);
	                if($table == 'wellinfo' && $field == 'tot'){
	                    $query = "update controllogs set tot = '$value'";
	                    $result = $db->DoQuery($query);
	                }
	                if($table == 'wellinfo' && $field == 'pterm_method'){
	                    ptermChange($value, $db);
	                }
	            }
	        }
	    }
	    $response = array("status"=>"success", "message" => "operation successful");
	} else {
    	$table = $_REQUEST['table'];
    	if(in_array($table,$allowed_tables)){
    		$table = mapToDbTable($table);
    		$field = $_REQUEST['field'];
    		$value = $_REQUEST['value'];
    		$query = "update $table set $field='$value';";
    		$result = $db->DoQuery($query);		
    		$response = array("status"=>"success", "message" => "operation successful");
    	} else {
    		$response = array("status"=>"failed", "message"=>"operation not allowed");
    	}
	}
} else {
	$response = array("status"=>"failed", "message"=>"seldbname not found");
}
echo json_encode($response);
exec("./sses_gva -d $seldbname ");
exec("./sses_cc -d $seldbname");
exec("./sses_cc -d $seldbname -p");
exec ("./sses_af -d $seldbname");
?>