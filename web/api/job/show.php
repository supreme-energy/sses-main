<?php 
require_once("../../dbio.class.php");

$seldbname = $_REQUEST['seldbname'];
$db=new dbio("sgta_index");
$db->OpenDb();

$db->DoQuery("SELECT * FROM dbindex where dbname = '$seldbname';");
$response = array();
$nowell = true;
while($db->FetchRow()) {
    $nowell = false;
    $id=$db->FetchField("id");
    $dbn=$db->FetchField("dbname");
    $dbreal=$db->FetchField("realname");
    $favorite = $db->FetchField("favorite");
    $db2 = new dbio($dbn);
    try{
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
    } catch(Exception $e){
        echo json_encode(array("status"=>"error", "message"=>"job $seldbname database does not exsist"));
        exit();
    }
}
if($nowell){
    echo json_encode(array("status"=>"error", "message"=>"job $seldbname database does not exsist"));
} else {
    echo json_encode(array_shift($response));
}
?>