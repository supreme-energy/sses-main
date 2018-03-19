<?php 
require_once("dbio.class.php");
$seldbname=$_REQUEST['seldbname'];
$color =  $_REQUEST['colortot'];
$color = str_replace('#','',$color);
$db=new dbio($seldbname);
$db->OpenDb();
$query="Update wellinfo set colortot='$color'";
$db->DoQuery($query);
header("Location: well_setup_marker_bed_import.php?seldbname=$seldbname");
?>