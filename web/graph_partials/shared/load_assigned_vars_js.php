<?php
if(!isset($seldbname) or $seldbname == '')	$seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');

if($seldbname!=''){
    require_once 'dbio.class.php';
    include("readwellinfo.inc.php");
    include("readappinfo.inc.php");
}
?>