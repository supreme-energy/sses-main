<?
	require_once("../dbio.class.php");
	include('../classes/RigMinderConnection.php');
	$rmc = new RigMinderConnection($_REQUEST);
	$result = $rmc-> prepare_las_data(6517,6549,1);
	print_r($result);
?>