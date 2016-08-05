<?php
	require_once("dbio.class.php");
	$seldbname=$_REQUEST['seldbname'];
	$refname =$_REQUEST['refwellname'];
	$query= "update wellinfo set refwellname='$refname'";
	$db=new dbio($seldbname);
	$db->OpenDb();
	$db->DoQuery($query);
?>