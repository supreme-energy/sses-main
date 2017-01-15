<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	require_once("dbio.class.php");
	require_once("classes/Survey.class.php");
	require_once("classes/Annotation.class.php");
	$survey_loader = new Survey($_REQUEST);
	$annos_loader = new Annotation($_REQUEST);
	$surveys=$survey_loader->get_surveys('rows',"ORDER BY md DESC");
	$showing = isset($_REQUEST['showing'])?$_REQUEST['showing']:0;
	$db=new dbio($_REQUEST['seldbname']);
	$db->OpenDb();
	include "readappinfo.inc.php";
	$db->CloseDb();
	if($showing){
		$anno_disp="display:none;";
		$anno_sel = "";
		$sgl_sel= "selected";
		$sgl_disp = "display:block";
	} else {
		$anno_disp = "display:block";
		$anno_sel = "selected";
		$sgl_sel= "";
		$sgl_disp = "display:none;";
	}
?>
<!doctype html>
<html>
<head>
<title>Annotations</title>
<link rel='stylesheet' type='text/css' href='projws.css'/>
<link rel='stylesheet' type='text/css' href='themes/blue/style.css'/>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
<script src="https://code.jquery.com/jquery-1.9.1.js"></script>
<script src="https://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
<link rel='stylesheet' type='text/css' href='jquery.timepicker.css'/>
<script type="text/javascript" src='jquery.timepicker.js'></script>
<script type="text/javascript" src='js/jquery.tablesorter.js'></script>
<script>
	$(function(){
		$("#date").datepicker({ dateFormat: 'mm/dd/yy' });
		$("#date").datepicker('setDate',new Date());
		$("#time").timepicker();
		$("#time").timepicker('setTime',new Date());
		$('#settcurrent').on('click', function (){
			$('#time').timepicker('setTime', new Date());
			$("#date").datepicker('setDate',new Date());
		});
		$("#anno_table").tablesorter({sortList:[[2,1]]});
	});
	function update_showing(){
		if($("#select_view").val()=='annotations'){
			$('#sgl_div_hidder_upper').hide()
			$('#annotations_div_hidder_upper').show()
			$('#annotations_div_hidder_lower').show()
			$('#annotations_table').show()
		} else{
			$('#sgl_div_hidder_upper').show()
			$('#annotations_table').hide()
			$('#annotations_div_hidder_upper').hide()
			$('#annotations_div_hidder_lower').hide()
		}
	}
</script>
<style>
#layer1 {
	position: fixed;
	visibility: hidden;
	background-color: #eee;
	border: 1px solid #000;
	padding: 0 2;
}
#layer1 input {
	border: none;
	background-color: transparent;
	color: blue;
	padding: 0 0;
	margin: 0;
}
#close {
	float: left;
}
</style>
</head>

<?if($showing==1) echo "<body onload='window.opener.location.reload();'>";
else echo "<body onload=''>";
?>


<table class='tabcontainer' style='width:100%;min-width:1024px;font-size:16px'>
<tr><td style='padding:10px 5px'>
	
	<select id='select_view' onchange="update_showing()">
		<option value='annotations' <?echo $anno_sel?>>annotations</option>
		<option value='sgl' <?echo $sgl_sel?>>survey graph labels</option>
	</select>
	<div id="sgl_div_hidder_upper" style="<?echo $sgl_disp?>">
		<form method='post' action='survey_label.php' name='label_settings' onsubmit='return false;'>
		<input type='hidden' name='seldbname' value='<?php echo $_REQUEST['seldbname']?>'>
		<table>
			
			<tr>
				<td >
					Label every <select name='label_every_cnt'>
									<option value=1 <?if($label_every==1)echo "selected";?>>survey</option>
									<option value=2 <?if($label_every==2)echo "selected";?>>other survey</option>
									<option value=3 <?if($label_every==3)echo "selected";?>>3rd survey</option>
									<option value=4 <?if($label_every==4)echo "selected";?>>4th survey</option>
									<option value=5 <?if($label_every==5)echo "selected";?>>5th survey</option>
									<option value=6 <?if($label_every==6)echo "selected";?>>6th survey</option>
									<option value=7 <?if($label_every==7)echo "selected";?>>7th survey</option>
									<option value=8 <?if($label_every==8)echo "selected";?>>8th survey</option>
									<option value=9 <?if($label_every==9)echo "selected";?>>9th survey</option>
									<option value=10 <?if($label_every==10)echo "selected";?>>10th survey</option>
								</select><br>
								<i>Labels always start from current survey depth</i>
				</td>
			</tr>
			<tr>
				<td >
					End depth:<input name='start_label_depth' type='text' value="<?echo $labeling_start?>">
				</td>
			</tr>
			<tr>
				<td>
					display MD:<input <?if($label_dmd==1)echo "checked";?> type="checkbox" name="md" value="true"/> VS:<input <?if($label_dvs==1)echo "checked";?>  type="checkbox" name="vs" value="true"/>
				</td>
			</tr>
			<tr>
				<td>
					display orientation <select name='label_orientation'><option value=0 <?if($label_orient==0)echo "selected";?>>vertical</option><option value=1 <?if($label_orient==1)echo "selected";?>>horizontal</option></select>
				</td>
			</tr>
			<tr>
				<td>
					include on reports <input <?if($label_dreport==1)echo "checked";?>  type="checkbox" name="rep_inc" value="true"/> wellbore plot <input <?if($label_dwebplt==1)echo "checked";?> type="checkbox" name="wbplt_inc" value="true"/>
				</td>
			</tr>
			<tr>
				<td>
					<input type='submit' value='Save Settings' onclick='document.label_settings.submit()'>
				</td>
			</tr>
			<tr>
					
		</table>
		</form>
	</div>
	<div id='annotations_div_hidder_upper' style="<?echo $anno_disp?>">
	<form method='post' action='annotation_create.php' name='annotation_form' onsubmit='return false;'>
	<input type='hidden' name='seldbname' value='<?php echo $_REQUEST['seldbname']?>'>
	<table>
		<tr>
		<td colspan='2'>
			<table>
				<tr><td>Date</td>
					<td ><input name='anno_date' type='text' id='date'></td>
					<td>Time</td>
					<td><input name ='anno_time' type='text' id='time'></td>
					<td><button id='settcurrent'>Set To Current Date and Time</button></td>
				</tr>
			</table>
		</td>
		<td>Choose Survey</td><td>
		<select name='annotation_survey'>
		<?foreach($surveys as $survey){
			if($survey['plan']==1)continue;
			?>
		<option value='<?php echo $survey['id']?>'>MD:<?php echo sprintf("%01.2f",$survey['md'])?>,INC:<?php echo sprintf("%01.2f",$survey['inc'])?>,VS:<?php echo sprintf("%01.2f",$survey['vs'])?></option>
		<?}?>
		</select></td>
		</tr>
		<tr><td colspan='4'>Annotation Comment<input type='text' value='' maxlength="45" size='45' name='comment'></td></tr>
		<tr><td colspan='4'><input type='submit' value='Add Annotation' onclick='document.annotation_form.submit()'></td></tr>
	</table>
	</form>
	</div>
</td>
</tr>
<tr><td style='padding:10px 5px'>
	<div id='annotations_div_hidder_lower' style="<?echo $anno_disp?>">
	<table width='100%'>
	<form method='post' action='annotation_settings.php' name='anno_settings' onsubmit='return false;'>
		<input type='hidden' name='seldbname' value='<?php echo $_REQUEST['seldbname']?>'>
		<tr><td colspan='6'>Select fields for display</td></tr>
		<tr>
		<td>MD:<input <?php echo $annos_loader->showCol('md')?'checked':''?> type="checkbox" name="md" value="true"/></td>
		<td>Footage:<input <?php echo $annos_loader->showCol('footage')?'checked':''?> type="checkbox" name="footage" value="true"/></td>
		<td>INC:<input <?php echo$annos_loader->showCol('inc')?'checked':''?> type="checkbox" name="inc" value="true"/></td>
		<td>AZM:<input <?php echo $annos_loader->showCol('azm')?'checked':''?> type="checkbox" name="azm" value="true"/></td>
		<td>AVG DIP:<input <?php echo $annos_loader->showCol('avg_dip')?'checked':''?> type="checkbox" name="avgd" value="true"/></td>
		<td>AVG GAS:<input <?php echo $annos_loader->showCol('avg_gas')?'checked':''?> type="checkbox" name="avgg" value="true"/></td>
		<td>AVG ROP:<input <?php echo $annos_loader->showCol('avg_rop')?'checked':''?> type="checkbox" name="avgr" value="true"/></td>
		<td>IN ZONE:<input <?php echo $annos_loader->showCol('in_zone')?'checked':''?> type='checkbox' name='inzn' value='true'/></td>
		<td>Comment:<input <?php echo $annos_loader->showCol('comment')?'checked':''?> type="checkbox" name="comment" value="true"/></td>
		</tr>
		<tr><td colspan='6'><input type='submit' value='Save Settings' onclick='document.anno_settings.submit()'></td></tr>
	</form>
	</table>
	</div>
</td></tr>
<tr><td style='padding-top:10px;' >
	<div id='annotations_table' style="<?echo $anno_disp?>">
	<?php include "annotation_list.php";?>
	</div>
</td></tr>
<tr>
<td colspan='16'>
	<br><center><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></center>
</td>
</tr>
</table>
</body >
</html>
