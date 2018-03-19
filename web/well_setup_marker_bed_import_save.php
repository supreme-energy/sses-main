<?php 
include_once("sses_include.php");
require_once("dbio.class.php");
$seldbname=$_REQUEST['seldbname'];
$db=new dbio("$seldbname");
$db->OpenDb();
$query ="delete from addforms";
$db->DoQuery($query);

foreach($_REQUEST as $key=>$value){
	if(strpos($key, "input_") !== false){
		$index_arr = explode('_',$key);
		$offset = $_REQUEST['value_'.$index_arr[1]];
		$label = $value;		
		$query = "insert into addforms (label,thickness,color) values ('$label','$offset','0000ff') ";		
		$db->DoQuery($query);
	}
}
header("Location: well_setup_tie_in.php?seldbname=$seldbname");
?>