<?php
include('include.php');
require_once("../dbio.class.php");
require_once('../classes/WellInfo.class.php');
require_once('../classes/WitsmlData.class.php');
$wellinfo = new WellInfo($_REQUEST); 
$witsml   = new WitsmlData($_REQUEST);
$body = '<trajectory uidWell="80f45c54-c9d7-4855-9197-aa06bcb6a2f4" uidWellbore="db39100f-037c-46fc-95f8-5859e748ded0">
					<name/><trajectoryStation uid="">' .
							'<md/>' .
							'<incl/>' .
							'<azi/>' .
							'<vertSect/>' .
							'</trajectoryStation>' .
							'</trajectory>';
$resp = $witsml->retrieve_fromstore($body,'trajectory');
//echo "REQUEST:".$witsml->client->__getLastRequest();
echo($resp['XMLout']);
?>
