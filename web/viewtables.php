<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<html>
<?
$seldbname=$_POST['seldbname'];
if(strlen($seldbname)<=0)	$seldbname=$_GET['seldbname'];
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
include("readwellinfo.inc.php");

$db->DoQuery("SELECT * FROM controllogs ORDER BY tablename");
if ($db->FetchRow()) {
	$controltot=$db->FetchField("tot");
	$controlbot=$db->FetchField("bot");
}
?>
<HEAD>
<LINK rel='stylesheet' type='text/css' href='viewtables.css'/>
<TITLE>View Tables</TITLE>
</HEAD>

<table class='tabcontainer'>
<tr>
<td>
	<TABLE class="surveys">
	<TR> 
	<TH class='col2' class='right'>DS</TH>
	<TH class='col1' colspan='2' class='right'>MD</TH>
	<TH class='col2' colspan='2' class='right'>TVD</TH>
	<TH class='col1' colspan='2' class='right'>VS</TH>
	<TH class='col2' class='right'>Dip</TH>
	<TH class='col2' class='right'>Fault</TH>
	<TH class='col1' colspan='2' class='right'>TOT/BOT</TH>
	<TH class='col2' class='right'>TOTW</TH>
	<TH class='col2' class='right'>BOTW</TH>
	</TR>
	<?
	$db->DoQuery("SELECT * FROM welllogs ORDER BY startmd DESC;");
	$num=$db->FetchNumRows(); 
	$i=1;
	while ($db->FetchRow()) {
		$id=$db->FetchField("id");
		$fault=sprintf("%.2f", $db->FetchField("fault"));
		$dip=sprintf("%.2f", $db->FetchField("dip"));
		$startmd=sprintf("%.2f", $db->FetchField("startmd"));
		$endmd=sprintf("%.2f", $db->FetchField("endmd"));
		$startvs=sprintf("%.2f", $db->FetchField("startvs"));
		$endvs=sprintf("%.2f", $db->FetchField("endvs"));
		$starttvd=sprintf("%.2f", $db->FetchField("starttvd"));
		$endtvd=sprintf("%.2f", $db->FetchField("endtvd"));
		$startdepth=sprintf("%.2f", $db->FetchField("startdepth"));
		$enddepth=sprintf("%.2f", $db->FetchField("enddepth"));
		$tot=sprintf("%.2f", $db->FetchField("tot"));
		$bot=sprintf("%.2f", $db->FetchField("bot"));
		?>
		<TR> 
		<TD class='col2'><?echo $num-$i+1;?></TD>
		<TD class='col1'><?echo $startmd;?></TD>
		<TD class='col1'><?echo $endmd;?></TD>
		<TD class='col2'><?echo $starttvd;?></TD>
		<TD class='col2'><?echo $endtvd;?></TD>
		<TD class='col1'><?echo $startvs;?></TD>
		<TD class='col1'><?echo $endvs;?></TD>
		<TD class='col2'><?echo $dip;?></TD>
		<TD class='col2'><?echo $fault;?></TD>
		<TD class='col2'><?echo $tot;?></TD>
		<TD class='col2'><?echo $bot;?></TD>
		<TD class='col2'><?printf("%.2f", $controltot-$startdepth);?></TD>
		<TD class='col2'><?printf("%.2f", $controlbot-$startdepth);?></TD>
		</TR>
	<?
		$i++;
	} 
	?>
	<TR> 
	<TH class='col2' class='right'>DS</TH>
	<TH class='col1' colspan='2' class='right'>MD</TH>
	<TH class='col2' colspan='2' class='right'>TVD</TH>
	<TH class='col1' colspan='2' class='right'>VS</TH>
	<TH class='col2' class='right'>Dip</TH>
	<TH class='col2' class='right'>Fault</TH>
	<TH class='col1' colspan='2' class='right'>TOT/BOT</TH>
	<TH class='col2' class='right'>TOTW</TH>
	<TH class='col2' class='right'>BOTW</TH>
	</TR>
	</TABLE>
</td>
</tr>
<tr>
<td colspan='12'>
	<br>
	<center>
	<small>
	<small>
	&#169; 2010-2011 Digital Oil Tools
	</small>
	</small>
	</center>
</td>
</tr>
</table>
</html>
<? $db->CloseDb(); ?>
