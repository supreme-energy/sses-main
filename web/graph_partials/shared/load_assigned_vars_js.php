<?php
if(!isset($seldbname) or $seldbname == '')	$seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
echo("console.log('$seldbname')\n\n");
if($seldbname!=''){
    require_once 'dbio.class.php';
    include("readwellinfo.inc.php");
    include("readappinfo.inc.php");
}
?>