<?php /*
	Written by: Richard R Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
//header("Location: gva_tab1.php");
include_once("sses_include.php");
require_once("dbio.class.php");
require_once("login.class.php");
require_once("version.php");
$db=new dbio("sgta_index");
$db->OpenDb();
$dbnames=array();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id DESC;");
while($db->FetchRow()) {
	$id=$db->FetchField("id");
	$dbids=$id;
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	$dbnames[]=$dbn;
	$realnames[]=$dbreal;
	if($seldbname==$dbn) {
		$dbrealname=$dbreal;
		$lastid=$id;
	}
}
?>
	<script>
		var selectWell = function(){
			var el = document.getElementById('selected_db')
			var db = el.options[el.selectedIndex].value
			window.open("/sses/gva_tab4.php?seldbname="+db+"&no_tabs=true",db,'resizable,height='+window.screen.height+',width=930px,left=0,top=0');
		}
	</script>
   <LINK href="gva_tab0.css?x=<?=time();?>" rel="stylesheet" type="text/css">
   <TABLE class='tabcontainer'>
   <TR>
       <TD>
           <TABLE style='margin: 2 0; padding: 0 0;' class='container'>
	           <TD> 
		       <img src='digital_tools_logo.png'  align='left'>
		       <H2 style='line-height: 2.0; font-style: italic; color: #040;' align='center'>Digital Oil Tools</H2>
		       <H1 style='line-height: 1.0;' align='center'>Subsurface Geological Tracking Analysis</H1>
		       <p align="center">Version <?echo $version; ?> (<?echo $_SERVER['SERVER_NAME']?>:<?echo $_SERVER['SERVER_PORT']?> - <?echo $hostname?>)</p>
		   </TD>
	   </TABLE>
	   <TABLE class='container'>
		<tr>
			<td>
			<FORM method='post' action='dbsaveas.php'>
			<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
			<INPUT type='hidden' name='dbname' value='sgta_template'>
			<INPUT type='hidden' name='newname' value='New Well'>
			<Input type='hidden' name='wizard' value='true'>
			<button>New Well</button>
			</FORM></td>
			<td>
			<select style='font-size: 10pt;' name='seldbname' id='selected_db'>
		<?
		$cnt=count($dbnames);
		for($i=0; $i<$cnt; $i++) {
			echo "<option value='{$dbnames[$i]}'";
			if($seldbname==$dbnames[$i])	echo " selected='selected'";
			echo ">{$realnames[$i]}</option>";
		}
		?>
		</select>
			<button onclick='selectWell()'>Select Well</button></td>
		</tr>
	  </TABLE>
</TD>
</TR>
</TABLE>


