<?php 
$db2=new dbio($seldbname);
$db2->OpenDb();
$db2->DoQuery("SELECT * FROM $tablename");
$data = array();
while($db2->FetchRow()) {
	array_push($data,array(
			"id" => $db2->FetchField("id"),
			"md" => $db2->FetchField("md"),
			"tvd" => $db2->FetchField("tvd"),
			"vs"  => $db2->FetchField("vs"),
			"value" => $db2->FetchField("value"),			
			"hide" => $db2->FetchField("hide"),
			"depth" => $db2->FetchField("depth")
	));
}
$db2->CloseDb();
?>