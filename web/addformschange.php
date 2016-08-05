<?php
$seldbname=$_POST['seldbname'];

do if(isset($_POST['infoid']) and is_numeric($_POST['infoid']))
{
	$infoid=$_POST['infoid'];
	$sql = "update addforms set ";

	// check for label

	if(isset($_POST['label']))
	{
		$sql .= "label = '" . str_replace("'","''",trim($_POST['label'])) . "', ";
	}

	// check for line color

	if(isset($_POST['color']))
	{
		$sql .= "color = '" . str_replace("#","",trim($_POST['color'])) . "', ";
	}

	if(isset($_POST['fill1']))
	{
		$sql .= "gnuplot_fill1 = '" . str_replace("'","''",trim($_POST['fill1'])) . "', ";
	}

	if(isset($_POST['fill2']))
	{
		$sql .= "gnuplot_fill2 = '" . str_replace("'","''",trim($_POST['fill2'])) . "', ";
	}

	if(isset($_POST['bg_color']))
	{
		$sql .= "bg_color = '" . trim($_POST['bg_color']) . "', ";
	}

	if(isset($_POST['bg_percent']))
	{
		if(is_numeric($_POST['bg_percent'])) $sql .= "bg_percent = " . trim($_POST['bg_percent']) . ", ";
		else $sql .= "bg_percent = 0, ";
	}

	if(isset($_POST['pat_color']))
	{
		$sql .= "pat_color = '" . trim($_POST['pat_color']) . "', ";
	}

	if(isset($_POST['pat_num']))
	{
		if(is_numeric($_POST['pat_num'])) $sql .= "pat_num = " . trim($_POST['pat_num']) . ", ";
		else $sql .= "pat_num = 0, ";
	}

	if($sql != "update addforms set ")
	{
		$sql = substr($sql, 0, -2) . " where id = $infoid";

		require_once("dbio.class.php");
		$db=new dbio($seldbname);
		$db->OpenDb();
		$db->DoQuery($sql);
		$db->CloseDb();
	}

	header("Location: gva_tab7.php?seldbname=$seldbname&infoid=$infoid");
	exit();

} while(false);

header("Location: gva_tab7.php?seldbname=$seldbname");
exit();
?>
