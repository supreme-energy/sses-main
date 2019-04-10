<?php
$db2=new dbio($seldbname);
$db2->OpenDb();
$db2->DoQuery("SELECT * FROM addformsdata WHERE infoid=$id order by md");
$data = array();
while($db->FetchRow()) {
	$tvd = $db2->FetchField("tvd");
	$tot = $db2->FetchField("tot");
	array_push($data,array(
			"id" => $db2->FetchField("id"),
			"md" => $db2->FetchField("md"),
			"tvd" => $tvd,
			"vs"  => $db2->FetchField("vs"),
			"tot" => $tot,
			"bot" => $db2->FetchField("bot"),
			"fault" => $db2->FetchField("fault"),
			"thickness" => $db2->FetchField("thickness"),
			"pos" => ($tot-$tvd)
	));
}
$db2->CloseDb();
?>