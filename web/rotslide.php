<?php
//  rotslide.php
//
//  Version: 1.1 (2015-04-06)
//
//  Purpose: To show the slide / rotate values and clculate the motor yield.
//
//  Written by: John P. Arnold
//  Copyright: 2009, Digital Oil Tools
//  All rights reserved.
//  NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//  or distribute this file in any manner without written permission of Digital Oil Tools

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'dbio.class.php';
require_once 'rotslidelib.php';

?>
<!doctype html>
<html>
<head>
<title>Slide Sheet</title>
<link rel='stylesheet' type='text/css' href='projws.css'/>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
<script src="https://code.jquery.com/jquery-1.9.1.js"></script>
<script src="https://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
<style>
body
{
	padding:5px 20px;
}
#main-div
{
	width:100%;
	max-width:850px;
	border:0;
	overflow:auto;
	margin:0 auto;
	border:1px solid black;
	color:black;
	background-color:#f0e6c8;
	font-size:14px;
}
#main-div table
{
	border-collapse:collapse;
	width:100%;
	font-size:13px;
	font-weight:normal;
	background-color:white;
}
#main-div table th,
#main-div table td
{
	border:1px solid #b0b0b0;
	height:20px;
	padding:0px 8px;
}
#main-div table td
{
	text-align:right;
}
#main-div table tr:nth-child(even) td:nth-child(n+6):nth-child(-n+9)
{
	background-color:#e0e0e0;
}
#main-div table tr:nth-child(odd) td,
#main-div table tr:nth-child(odd) td input[type=text]
{
	background-color:#ddffdd;
}
#main-div table td input[type=text]
{
	border:0;
	width:50px;
}
#main-div table td:nth-child(5) input[type=text]
{
	width:30px;
}
#main-div input[type=submit],
#main-div input[type=reset],
#main-div input[type=button]
{
	padding:0px 30px;
}
</style>
</head>
<body>
<?php
if(!isset($_REQUEST['seldbname']) or trim($_REQUEST['seldbname']) == '')
{
	echo "<p>Did Not Define Database Name</p>";
	exit();
}
//echo '<pre>'; print_r($_REQUEST); echo '</pre>';
$seldbname = $_REQUEST['seldbname'];
$db = new dbio($seldbname);
$db->OpenDb();
$db->DoQuery('select * from wellinfo');
$wellinfo = $db->FetchObj();
?>
<div id='main-div'>
<div style='padding:5px'>
	<div style='float:left'>
		<div style='font-size:20px;font-weight:bolder;padding:5px'>Slide Sheet</div>
		<div style='padding:5px'><?php echo "{$wellinfo->wellborename} / {$wellinfo->operatorname}" ?></div>
	</div>
	<div style='clear:both'></div>
</div>
<div style='padding:5px;border-top:1px solid gray;min-height:200px'>
<?php

// check if anything was passed

if(isset($_REQUEST['s']) and isset($_REQUEST['s']['data']) and is_array($_REQUEST['s']['data']) and
	count($_REQUEST['s']['data']) > 0)
{
	foreach($_REQUEST['s']['data'] as $rsid => $vals)
	{
		//echo "<p>rsid=$rsid</p>";
		if($rsid < 10000 and is_numeric($rsid))
		{
			if(isset($vals['delete']) and $vals['delete'] = 'on')
			{
				$sql = "delete from rotslide where rsid = $rsid";
			}
			else
			{
				$rotstartmd = (trim($vals['rotstartmd']) == '' ? '0' : trim($vals['rotstartmd']));
				$rotendmd = (trim($vals['rotendmd']) == '' ? '0' : trim($vals['rotendmd']));
				$slidestartmd = (trim($vals['slidestartmd']) == '' ? '0' : trim($vals['slidestartmd']));
				$slideendmd = (trim($vals['slideendmd']) == '' ? '0' : trim($vals['slideendmd']));
				$tfo = trim($vals['tfo']);
				$sql = "update rotslide set rotstartmd = '$rotstartmd', rotendmd = '$rotendmd', slidestartmd = '$slidestartmd', " .
					"slideendmd = '$slideendmd', tfo = '$tfo' where rsid = $rsid";
			}
			//echo "<p>sql=$sql</p>";
			$db->DoQuery($sql);
		}
		elseif($rsid >= 10000 and is_numeric($rsid))
		{
			$rotstartmd = (trim($vals['rotstartmd']) == '' ? '0' : trim($vals['rotstartmd']));
			$rotendmd = (trim($vals['rotendmd']) == '' ? '0' : trim($vals['rotendmd']));
			$slidestartmd = (trim($vals['slidestartmd']) == '' ? '0' : trim($vals['slidestartmd']));
			$slideendmd = (trim($vals['slideendmd']) == '' ? '0' : trim($vals['slideendmd']));
			$tfo = trim($vals['tfo']);

			if((intval($rotstartmd) > 0 and intval($rotendmd) > 0 and intval($rotendmd) > intval($rotstartmd)) or
				(intval($slidestartmd) > 0 and intval($slideendmd) > 0 and intval($slideendmd) > intval($slidestartmd)))
			{
				$sql = "insert into rotslide (rotstartmd,rotendmd,slidestartmd,slideendmd,tfo) " .
					"values ($rotstartmd,$rotendmd,$slidestartmd,$slideendmd,'$tfo')";
				//echo "<p>sql=$sql</p>";
				$db->DoQuery($sql);
			}
		}
	}
}

// first recalculate the current data

RecalcValuesInIntervals($db);

// read the current data

$db->DoQuery('select * from rotslide order by rsid');

$cols = array(
	array('rotstartmd','Rot Strt','text'),
	array('rotendmd','Rot End','text'),
	array('slidestartmd','Slide Strt','text'),
	array('slideendmd','Slide End','text'),
	array('tfo','TFO','text'),
	array('md','Svy-MD',''),
	array('bur','BUR',''),
	array('turn_rate','Turn Rate',''),
	array('motor_yield','Motor Yield',''));
?>

<form method='post' action='rotslide.php'>
<input type='hidden' name='seldbname' value='<?php echo $seldbname ?>' />
<table>
<tr>
<?php
foreach($cols as $col) echo "<th>{$col[1]}</th>";
echo "<th><span style='color:red'>X</span></th></tr>\n";
while(($row = $db->FetchObj()))
{
	echo "<tr>";
	foreach($cols as $col)
	{
		echo "<td>";
		if($col[2] == 'text')
		{
			echo "<input type='text' name='s[data][{$row->rsid}][{$col[0]}]' value='";
		}
		if($row->$col[0] == '0') echo '';
		else echo $row->$col[0];
		if($col[2] == 'text')
		{
			echo "' />";
		}
		echo "</td>";
	}
	echo "<td style='text-align:center'><input type='checkbox' name='s[data][{$row->rsid}][delete]' /></td>";
	echo "</tr>\n";
}
for($i=0; $i<5; $i++)
{
	echo "<tr>";
	$rsid = 10000 + $i;
	foreach($cols as $col)
	{
		echo "<td>";
		if($col[2] == 'text')
		{
			echo "<input type='text' name='s[data][{$rsid}][{$col[0]}]' value='' />";
		}
		echo "</td>";
	}
	echo "<td></td></tr>\n";
}
?>
</table>
<div style='padding:10px 50px'>
	<div style='float:left;width:25%;text-align:center'><input type='submit' value='Save' /></div>
	<div style='float:left;width:25%;text-align:center'><input type='reset' value='Undo' /></div>
	<div style='float:left;width:25%;text-align:center'><input type='button' value='Print...' onclick='self.print()' /></div>
	<div style='float:left;width:25%;text-align:center'><input type='button' value='Close' onclick='self.close()' /></div>
	<div style='clear:both'></div>
</div>
</div>
</div>
</body>
</html>
<?php
$db->CloseDb();
?>
