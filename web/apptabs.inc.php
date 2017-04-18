<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

$userlevel = (isset($_SESSION['userlevel']) ? $_SESSION['userlevel'] : '');

$currentFile=basename($_SERVER['REQUEST_URI'], ".php"); /* supposing filetype .php*/
$tabnames=array();
$tabnames[]="Front Page";
$tabnames[]="Wellplan/Control";
$tabnames[]="Target Tracker";
$tabnames[]="SGTA Modeling";
$tabnames[]="Wellbore Plots";
$tabnames[]="Additional Data";
$tabnames[]="Formations";
$tabnames[]="Reports";
$tabnames[]="Combo";
if ($userlevel == 'ADMIN')
    $tabnames[]="Administration";
if ($userlevel == 'SUPER_USER')
    $tabnames[]="Administration";

$tablinks=array();
$tablinks[]="gva_tab1.php";
$tablinks[]="gva_tab2.php";
$tablinks[]="gva_tab3.php";
$tablinks[]="gva_tab4.php";
$tablinks[]="gva_tab5.php";
$tablinks[]="gva_tab6.php";
$tablinks[]="gva_tab7.php";
$tablinks[]="reports_tab.php";
$tablinks[]="gva_tab8.php";
if ($userlevel == 'ADMIN') $tablinks[]="gva_admin.php";
if ($userlevel == 'SUPER_USER') $tablinks[]="gva_suadmin.php";
?>
<table class='tabs'>
	<tr>
<?php
for($i=0;$i<count($tabnames);$i++) {
//	if($sgta_off){
//		if($tabnames[$i]=="SGTA Modeling"){
//			$display='display:none';
//			$hrfdisp='display:none'; 
//		}else{
//			$display='';
//			$hrfdisp='display:inline'; 
//		}
//	} else {
//		$display='display:inline';
//		$hrfdisp='display:inline';
//	}
	if($maintab==$i )
		echo "		<td class='active'><a class='tabs' href='{$tablinks[$i]}?seldbname=$seldbname'>{$tabnames[$i]}</a></td>\n";
	else
		echo "		<td class='inactive'><a class='tabsinactive' href='{$tablinks[$i]}?seldbname=$seldbname'>{$tabnames[$i]}</a></td>\n";
}
?>
	</tr>
</table>

<!--
<script type='text/javascript' src='ext-all.js'></script>
<script>
var pageMask = new Ext.LoadMask(Ext.getBody(), {msg:\"Loading. Please wait...\"});
function callMask(show){if(show){pageMask.show()}else{pageMask.hide()}}
</script>
-->

<style type='text/css'>
.x-mask {
	z-index: 100;
	position: absolute;
	top: 0;
	left: 0;
	filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=50 );
	opacity: 0.5;
	width: 100%;
	height: 100%;
	zoom: 1;
	background: #cccccc
}

.x-mask-msg {
	z-index: 20001;
	position: absolute;
	top: 0;
	left: 0;
	padding: 2px;
	border: 1px solid;
	border-color: #d0d0d0;
	background-image: none;
	background-color: #e0e0e0
}

.x-mask-msg div {
	padding: 5px 10px 5px 25px;
	background-image:
		url('../../resources/themes/images/gray/grid/loading.gif');
	background-repeat: no-repeat;
	background-position: 5px center;
	cursor: wait;
	border: 1px solid #b3b3b3;
	background-color: #eeeeee;
	color: #222222;
	font: normal 11px tahoma, arial, verdana, sans-serif
}
</style>
