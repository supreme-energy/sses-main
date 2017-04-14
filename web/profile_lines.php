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
<title>Profile Lines</title>
</HEAD>
<BODY>
<table class='tabcontainer' style="width:100%"><tr><td>
Select Well: <select style='font-size: 10pt;' name='seldbname'>
		<?
		$cnt=count($dbnames);
		for($i=0; $i<$cnt; $i++) {
			$dbname_to_realname_map[$dbnames[$i]]=$realnames[$i];
			echo "<option value='{$dbnames[$i]}'";
			if($seldbname==$dbnames[$i])	echo " selected='selected'";
			echo ">{$realnames[$i]}</option>";
		}
		?>
		</select>
		<button>Add</button>

</td></tr>
<tr><td>
<?php while($db2->FetchRow()){?>
	<div style="clear:both;">
		<div style="float:left">
			<?= $db2->FetchField("reference_database")?>
		</div>
		<div style="float:left">
			<?= $dbname_to_realname_map[$db2->FetchField("reference_database")]?>
		</div>
		<div style="float:left">
			<?= $db2->FetchField("color")?>
		</div>
		<div style="float:right">
			<button>update</button><br>
			<button>delete</button>
		</div>
	</div>
<?php }?>
</td></tr>
</table>
</BODY>
</HTML>