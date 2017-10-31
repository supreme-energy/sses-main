<?
	require_once("dbio.class.php");
	require_once("classes/Annotation.class.php");
	if(!isset($anno_loader)){
		$annos_loader = new Annotation($_REQUEST);
	}
	$annos = $annos_loader->get_all('rows');
	$db->OpenDb();
?>
<style>
#anno_table {
	width:100%;
	border-collapse:separate;
	background-color:#2C4C69;
}
#anno_table th {
	text-align:left;
}
</style>

<?php
	require_once 'annotation_lib.php';
	AnnotationsCalcInZone($db,$annos);
?>

<table id='anno_table' class='tablesorter'>
<thead>
<tr>
<th class='surveys'>DATE</th>
<th class='surveys'>TIME</th>
<th class='surveys'>MD</th>
<th class='surveys'>Footage</th>
<th class='surveys'>INC</th>
<th class='surveys'>AZM</th>
<th class='surveys'>AVG DIP</th>
<th class='surveys'>AVG GAS</th>
<th class='surveys'>AVG ROP</th>
<th class='surveys'>In-Zone</th>
<th class='surveys'>Comment</th>
<td></td>
</tr>
</thead>
<tbody>
<?php 
	$sdepth=0;
	$edepth=0;
	foreach($annos as $anno)
	{
		$edepth = $anno['md'];
		$avgs = $annos_loader->get_avgs($sdepth,$edepth);
		$sdepth = $edepth;
?>
	<tr>
		<td><? echo date('m/d/Y', strtotime($anno['assigned_date']))?></td>
		<td><? echo date('h:i a', strtotime($anno['assigned_date']))?></td>
		<td><? echo sprintf("%01.2f",$anno['md'])?></td>
		<td><? echo sprintf("%01.2f",$avgs['footage'])?></td>
		<td><? echo sprintf("%01.2f",$anno['inc'])?></td>
		<td><? echo sprintf("%01.2f",$anno['azm'])?></td>
		<td><? echo sprintf("%01.2f",$avgs['dip']) ?></td>
		<td><? echo sprintf("%01.2f",$avgs['gas']) ?></td>
		<td><? echo sprintf("%01.2f",$avgs['rop']) ?></td>
		<td><? echo $anno['inzn'] ?></td>
		<td width='200px'><? echo $anno['detail_assignments']?></td>
		<td><input type='submit' value='delete' onclick="window.location='annotation_delete.php?seldbname=<?php echo $_REQUEST['seldbname']?>&aid=<?php echo $anno['id']?>'"></td>
	</tr>
<?php
	}
?>
</tbody>
</table>
<?php
	$db->CloseDb();
?>
