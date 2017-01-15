<?php
/*
 * Created on Aug 12, 2013
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("dbio.class.php");
$seldbname=$_REQUEST['seldbname'];
$method = $_REQUEST['method'];
$query = "update wellinfo set pterm_method='$method'";
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery($query);
header("location: gva_tab3.php?seldbname=$seldbname");
?>
