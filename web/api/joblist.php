<?php 
require_once("../dbio.class.php");

$fav_only = (isset($_REQUEST['favorite']) ? true : false);

$db=new dbio("sgta_index");
$db->OpenDb();
$filter_add = '';
if($fav_only){
	"where favorite = 1";
}
$db->DoQuery("SELECT * FROM dbindex $filter_add ORDER BY id DESC;");
$response = array();
while($db->FetchRow()) {
	$id=$db->FetchField("id");
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	$favorite = $db->FetchField("favorite");
	$db2 = new dbio($dbn);
	try {
	   $db2->OpenDb();
	} catch (Exception $e){
	    $db2=null;
	}
	if($db2){
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
    	$map_zone = $db2->FetchFIeld("map_zone");
    	array_push($response, array("sgta_index_id" => $id, "jobname" => $dbn, "realjobname" => $dbreal,
    			"favorite" => $favorite,
    			"pbhl_easting" => $pbhl_easting,
    			"pbhl_northing" => $pbhl_northing,
    			"survey_easting" => $survey_easting,
    			"survey_northing" => $survey_northing,
    			"landing_easting" => $landing_northing,
    			"elev_groun" => $elev_ground,
    			"elev_rkb"   => $elev_rkb,
    			"correction" => $correction,
    			"coordsys"   => $coordsys,
    			"map_zone"   => $map_zone
    	));	
    	$db2->CloseDb();
	}
}
echo json_encode($response);	
?>