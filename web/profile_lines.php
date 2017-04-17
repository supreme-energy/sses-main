<?php 
require_once 'sses_include.php';
require_once 'dbio.class.php';
if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : '');
if($seldbname == '') include('dberror.php');
$dbids=array();
$dbnames=array();
$realnames=array();
$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id DESC;");
while($db->FetchRow()) {
	$id=$db->FetchField("id");
	$dbids[]=$id;
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	$dbnames[]=$dbn;
	$realnames[]=$dbreal;
	if($seldbname==$dbn) {
		$dbrealname=$dbreal;
		$lastid=$id;
	}
}
$db2 = new dbio($seldbname);
$db2->OpenDb();
$db2->DoQuery("select * from profile_lines;");
$dbname_to_realname_map = array();
?>
<HTML>
<HEAD>
<link rel="stylesheet" type="text/css" href="gva_tab2.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<link rel='stylesheet' type='text/css' href='projws.css'/>
<link rel='stylesheet' type='text/css' href='themes/blue/style.css'/>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
<script src="https://code.jquery.com/jquery-1.9.1.js"></script>
<script src="https://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
<link rel='stylesheet' type='text/css' href='jquery.timepicker.css'/>
<script type="text/javascript" src='jquery.timepicker.js'></script>
<script type="text/javascript" src='js/jquery.tablesorter.js'></script>
<title>Profile Lines</title>
<style>
#selected_reference_table {
	width:100%;
	border-collapse:separate;
	background-color:#307040;
}
#selected_reference_table th {
	text-align:left;
}
</style>
<script>
function openColorChoiceWellPlan(rowform) { 
    var phpcall="wellplancolorwp.php?seldbname="+rowform.seldbname.value+"&colorbot="+rowform.colorrawwp.value;
    newwindow=window.open(phpcall,'ColorChoice', 'height=300,width=300,scrollbars=no');
    if (window.focus) {newwindow.focus()}
    // return false;
	return ray.ajax();
}
</script>
</HEAD>
<BODY>
<table class='tabcontainer' style="width:100%"><tr><td>
<form ACTION="profile_lines_add.php">
<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
Select Well: <select style='font-size: 10pt;' name='dbname'>
		<?
		$cnt=count($dbnames);
		for($i=0; $i<$cnt; $i++) {
			$dbname_to_realname_map[$dbnames[$i]]=$realnames[$i];
			if($seldbname==$dbnames[$i]) continue;
			echo "<option value='{$dbnames[$i]}'";
			echo ">{$realnames[$i]}</option>";
		}
		?>
		</select>
		<button>Add</button>
</form>
</td></tr>
<tr><td>
	<table id='selected_reference_table' class='tablesorter'>
	<thead>
	<tr>
	<th class='surveys'>ID</th>
	<th class='surveys'>Well Name</th>
	<th class='surveys'>Line Color</th>
	<th class='surveys'>Actions</th>
	</tr>
	</thead>
	<tbody>
	<?php while($db2->FetchRow()){?>
		<tr>
		<td><?= $db2->FetchField("reference_database")?></td>
		<td><?= $dbname_to_realname_map[$db2->FetchField("reference_database")]?></td>
		<td style='background-color:#<?=$db2->FetchField("color");?>'>
		 <form id="color_update_form_<?= $db2->FetchField("reference_database")?>" action="profile_lines_add.php">
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type="hidden" name="dbname" value="<?= $db2->FetchField("reference_database")?>">
			<input type="text" readonly="true" size='7' id="color" name="color" 
				value="<?= $db2->FetchField("color")?>" 
				style="vertical-align:bottom;background-color:#<?= $db2->FetchField("color")?>;color:white;"
				onclick='openColorChoiceWellPlan(this.form);'/> 
			</form>
		 
		</td>
		<td>
		 <table>
			<tr><td><button>update</button></td></tr>
		    <tr><td><form action="profile_lines_delete.php">
		    <input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
		    <input type="hidden" name="dbname" value="<?= $db2->FetchField("reference_database")?>">
		    <button>delete</button></form></td></tr>
		 </table>
		</td>
	</tr>
	<?php }?>
	</tbody>
	</table>
</td></tr>
</table>
</BODY>
</HTML>