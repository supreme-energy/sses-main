<?
require_once("dbio.class.php");
$ret=$_GET['ret'];
$seldbname = $_REQUEST['seldbname'];
if($seldbname){
echo "seldb found";
$db=new dbio($seldbname);
$db->OpenDb();
	$allowed_tables=array("wellinfo","appinfo");
	$table = $_REQUEST['table'];
	if(in_array($table,$allowed_tables)){
		echo "in allowed table set";
		$field = $_REQUEST['field'];
		$value = $_REQUEST['value'];
		$query = "update $table set $field='$value';";
		echo $query;
		$db->DoQuery($query);
	}
}
?>