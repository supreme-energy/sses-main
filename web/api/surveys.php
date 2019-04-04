 <?php
 include("api_header.php");
 include("../readsurveys.inc.php");
 echo json_encode($srvys_joined);
 ?>