 <?php
 include("api_header.php");	
 include("../readwellinfo.inc.php");
 include("../readappinfo.inc.php");
 $final_wellinfo = array(
 		"appinfo"  => $appinfo_joined,
 		"wellinfo" => $wellinfo_joined,
 		"autorc"   => $autorc_joined,
 		"witsml_details"   => $witsml_joined,
 		"emailinfo"    => $emailinfo_joined
 );
 echo json_encode($final_wellinfo);