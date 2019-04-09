<?php 
require_once("../dbio.class.php");

$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id DESC;");
$response = array();
while($db->FetchRow()) {
	$id=$db->FetchField("id");
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	$db2 = new dbio($dbn);
	$db2->OpenDb();
	$db2->DoQuery("select * from wellinfo");
	$db2->FetchRow();
	$pbhl_easting=$db2->FetchField("pbhl_easting");
	$pbhl_northing=$db2->FetchField("pbhl_northing");
	$survey_easting=$db2->FetchField("survey_easting");
	$survey_northing=$db2->FetchField("survey_northing");
	$landing_easting=$db2->FetchField("landing_easting");
	$landing_northing=$db2->FetchField("landing_northing");
	$elev_ground=$db2->FetchField("elev_ground");
	$elev_rkb=$db2->FetchField("elev_rkb");
	$correction=$db2->FetchField("correction");
	$coordsys=$db2->FetchField("coordsys");
	array_push($response, array("sgta_index_id" => $id, "jobname" => $dbn, "realjobname" => $dbreal,
			"pbhl_easting" => $pbhl_easting,
			"pbhl_northing" => $pbhl_northing,
			"survey_easting" => $survey_easting,
			"survey_northing" => $survey_northing,
			"landing_easting" => $landing_northing,
			"elev_groun" => $elev_ground,
			"elev_rkb"   => $elev_rkb,
			"correction" => $correction,
			"coordsys"   => $coordsys
	));	
	$db2->CloseDb();
}
echo json_encode($response);	
?>