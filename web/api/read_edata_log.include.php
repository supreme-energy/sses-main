<?php
$db2=new dbio($seldbname);
$db2->OpenDb();
$db2->DoQuery("SELECT * FROM $tablename order by md");
$data = array();
while($db2->FetchRow()) {
	$tvd = $db2->FetchField("tvd");
	$tot = $db2->FetchField("tot");
	array_push($data,array(
			"id" => $db2->FetchField("id"),
			"md" => $db2->FetchField("md"),
			"tvd" => $tvd,
			"vs"  => $db2->FetchField("vs"),
	        "value" => $db2->FetchField("value")			
	));
}
$db2->CloseDb();
?>