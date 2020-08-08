<?php
try {
    require_once("../dbio.class.php");
    require_once('../classes/WellInfo.class.php');
    require_once('../classes/WitsmlData.class.php');
    $wellinfo = new WellInfo($_REQUEST); 
    $witsml   = new WitsmlData($_REQUEST);
    $wellUid = $_REQUEST['uidwell'];
    $body = "<wellbore uidWell='$wellUid'><nameWell/><name/><numGovt/></wellbore>";
    $resp = $witsml->retrieve_fromstore($body,'wellbore');
    //echo "REQUEST:".$witsml->client->__getLastRequest();
    $xml = array('status'=>'error', 'message'=>'soap fault');
    if(!is_a($resp, SoapFault)){
        $xml = simplexml_load_string($resp['XMLout']);
    }
    echo json_encode($xml);
} catch(Exception $e){
    echo json_encode(array('status' => 'error', 'message' => $e->toString()));
}
?>
