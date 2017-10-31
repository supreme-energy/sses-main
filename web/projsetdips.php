<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$dip=$_POST['dip'];
$ret=$_POST['ret'];
$calcdipset=$_REQUEST['calcdipset'];
$startsvy = $_POST['svy_start'];
$endsvy = $_POST['svy_end'];
$svy_dip = $_POST['svy_dip'];


require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
$dbout=new dbio($seldbname);
$dbout->OpenDb();

$db->DoQuery("UPDATE wellinfo SET projdip='$dip';");
//print "UPDATE wellinfo SET projdip='$dip';";
$db->DoQuery("SELECT * FROM surveys ORDER BY md asc");
$cnt=0;
while($db->FetchRow()) {
	$id=$db->FetchField("id");
	$plan=$db->FetchField("plan");
	$curvs=$db->FetchField("vs");
	$curtot=$db->FetchField("tot");
	$curbot=$db->FetchField("bot");
	$curtvd=$db->FetchField("tvd");
	if($plan>0) {
		$tvd=$lasttvd+(-tan($dip/57.29578)*($curvs-$lastvs));
		$tot=$lasttot+(-tan($dip/57.29578)*($curvs-$lastvs));
		$bot=$lastbot+(-tan($dip/57.29578)*($curvs-$lastvs));
		$dbout->DoQuery("UPDATE surveys SET dip='$dip',tot='$tot',bot='$bot',tvd='$tvd' WHERE id=$id;");
	} else {
		if($cnt<=$endsvy && $cnt>=$startsvy){
			//echo 'executing at:'."UPDATE surveys set dip='$svy_dip' where id=$id".'<br>';
			$dbout->DoQuery("UPDATE surveys set dip=$svy_dip where id=$id");
		}
	}
	$lastvs=$curvs;
	$lasttot=$curtot;
	$lastbot=$curbot;
	$lasttvd=$curtvd;
	$cnt++;
}

$db->DoQuery("SELECT * FROM surveys ORDER BY md DESC LIMIT 1;");
if($db->FetchRow()) {
	$lasttot=$db->FetchField("tot");
	$lastbot=$db->FetchField("bot");
	$lastvs=$db->FetchField("vs");
	$lasttvd=$db->FetchField("tvd");

	$db->DoQuery("SELECT * FROM projections ORDER BY md ASC;");
	$dbout->DoQuery("BEGIN TRANSACTION");
	while ($db->FetchRow()) {
		
		$id=$db->FetchField("id");
		$method=$db->FetchField("method");
		$data=$db->FetchField("data");
		$tot=$db->FetchField("tot");
		$bot=$db->FetchField("bot");
		$vs=$db->FetchField("vs");
		$tvd=$db->FetchField("tvd");

		$tot=$lasttot+(-tan($dip/57.29578)*($vs-$lastvs));
		$bot=$lastbot+(-tan($dip/57.29578)*($vs-$lastvs));
		//$tvd=$lasttvd+(-tan($dip/57.29578)*($vs-$lastvs));
		
		$tot=sprintf("%.2f", $tot);
		$bot=sprintf("%.2f", $bot);
		$tvd=sprintf("%.2f", $tvd);

		if($method==8) {
			// written from projwsd as: if($method==8) $data="$vs,$tpos,$dip,$fault";
			
			$line=trim($data);
			$line=preg_replace( '/\s+/', ',', $line );
			$d=explode(",", $line);
			//print_r($d);
			$vs=$d[0];
			$tpos=$d[1];
			$fault=$d[3];
			$tvd=$tot-$tpos;
			$data="$vs,$tpos,$dip,$fault";
		}
		//if($method==7 || $method==6) {
		if($method==7) {
			$line=trim($data);
			$line=preg_replace( '/\s+/', ',', $line );
			$d=explode(",", $line);
			$tpos=$d[2];
			if($method==6) {
				$tvd=$d[0];
				$data="$tvd,$vs,$tpos";
			}
			else {
				$tvd=$tot-$tpos;
				$data="$tot,$vs,$tpos";
			}
			
		}
		$dbout->DoQuery("UPDATE projections SET data='$data',dip='$dip',tot='$tot',bot='$bot',tvd='$tvd' WHERE id=$id;");
		$lasttvd=$tvd;
		$lasttot=$tot;
		$lastbot=$bot;
		$lastvs=$vs;
	}
	$dbout->DoQuery("COMMIT");
}
$dbout->CloseDb();
$db->CloseDb();
// sleep(5);
//exec("./sses_cc -d $seldbname -p");
//exec ("./sses_af -d $seldbname");

//header("Location: projavgdip.php?seldbname=$seldbname");
// include("$ret");
?>
<script>
	if(window.opener && !window.opener.closed) {
		window.opener.location.reload();
	}
	window.location='projavgdip.php?seldbname=<?echo $seldbname?>&calcdipset=<?echo $calcdipset?>';
</script>
<BODY onload='recalc();'>
<CENTER>
<TABLE class='tabcontainer' style='width=300px'><tr><td>Saving...</td></tr></table></body>