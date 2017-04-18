<?php 
require_once("dbio.class.php");
$seldbname=$_GET['seldbname'];
if("$seldbname"=="") include("dberror.php");
$db=new dbio($seldbname);
$db->OpenDb();

$anticollisionwell_id = isset($_REQUEST['acwellid'])?$_REQUEST['acwellid']:false;
$sql = "select * from anticollision_wells";
$db->DoQuery($sql);
$select_array = array();
while($db->FetchRow()){
	if($anticollisionwell_id===false){
		$anticollisionwell_id=$db->FetchField('id');
		$selected = 'selected';
	}else if($anticollisionwell_id==$db->FetchField('id')){
		$selected = 'selected';
	} else {
		$selected = '';
	}
	array_push($select_array,"<option $selected value='".$db->FetchField('id')."'>".$db->FetchField('realname')."</option>");
}
if(count($select_array)==0){
	array_push($select_array,"<option value=''>------------------------------------------------------</option>");
}
if($anticollisionwell_id!==false){
	$sql = "select * from anticollision_wells where id = $anticollisionwell_id";
	$db->DoQuery($sql);
	$row = $db->FetchRow();
	extract($row);
}
?>
<HTML>
<HEAD>
<link rel="stylesheet" type="text/css" href="gva_tab2.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<title>AntiCollision</title>
<script>
	changeWell=function(){
		selectedid = document.getElementById('selectedacwell').value
		if(selectedid != '<?php echo $anticollisionwell_id?>'){
			window.location = "anticollisionwells.php?seldbname=<?php echo $seldbname?>&acwellid="+selectedid;
		}
	}
	doSubmit =function(rowform)
	{
		rowform.scrolltop.value=document.getElementById("scrollContent").scrollTop;
		t = 'anticollision_changesurvey.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return ray.ajax();
	}
	OnSubmit=function(rowform)
	{
		t = 'anticollision_changesurvey.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return true;
	}
	OnDelSurveys=function()
	{
		var numsvys=document.getElementById("numsvys").value;
		var sids="";
		for(i=0; i<numsvys; i=i+1) {
			fid="f"+i;
			form=document.getElementById(fid);
			dodel=form.del.checked;
			id=form.id.value;
			if(dodel>0) {
				if(sids=="") sids=id;
				else sids=sids+","+id;
			}
		}
		if(sids=="")	return;
		rowform=document.getElementById("delsvys");
		rowform.sids.value=sids;
		result=confirm("Delete anticollision survyers, are you sure?");
		if(result)
		{
			t = 'anticollision_deletesurvey.php';
			t = encodeURI (t); // encode URL
			rowform.action = t;
			rowform.submit(); // submit form using javascript
			return ray.ajax();
		}
	}
	function OnClearChecks()
	{
		var numsvys=document.getElementById("numsvys").value;
		var sids="";
		for(i=0; i<numsvys; i=i+1) {
			fid="f"+i;
			form=document.getElementById(fid);
			form.del.checked=0;
		}
	}
	function OnSetChecks()
	{
		var numsvys=document.getElementById("numsvys").value;
		var sids="";
		for(i=0; i<numsvys; i=i+1) {
			fid="f"+i;
			form=document.getElementById(fid);
			form.del.checked=1;
		}
	}	
</script>
</HEAD>
<BODY>
<table class='tabcontainer' style='width:1000px'>
<tr>
<td>
	<table style='width:996px'>
	<tr>
		<td>
			<table class='surveys' style='padding:2;width:996px;'>
				<tr>
					<td width="300px"><form method='GET' action="anticollision_createnew.php">
						<input type='hidden' name='seldbname' value="<?php echo $seldbname?>">
						<input size='30' style='text-align:left' type='text' name='cn' placeholder='Anti-Collision Well Name'><input type='submit' value='create new'>
						</form></td>
					<td><select id='selectedacwell' onchange="changeWell()"><?php echo implode('',$select_array) ?></select></td>
					<?php if($anticollisionwell_id!==false){?>
					<td><form action='anticollision_rename.php' method='GET'>
					<input type='hidden' name='seldbname' value="<?php echo $seldbname?>">
					<input type='hidden' name='cid' value = "<?php echo $anticollisionwell_id?>">
					<input type='text' name='newacn' value='<?php echo $realname?>' placeholder='new name'><input type='submit' value='Rename'></form></td>
					<td><form action='anticollision_delete.php' method='GET'>
						<input type='hidden' name='seldbname' value="<?php echo $seldbname?>">
						<input type='hidden' name='cid' value = "<?php echo $anticollisionwell_id?>">
						<input type='submit' value='Delete'></form></td>
					<?}?>
			</table>
		</td>
	</tr>
	<tr>
	<td>
		<?if($anticollisionwell_id===false){?>
		<table class='surveys' style="width:996px;">
		<TR>
		<td>
			Select an Anti-Collision Well or Create a new one
		</td>
		</tr>
		</TABLE>
		<?} else {?>
				<table class='surveys'>
		<tr>
		<td>
			<form action="anticollision_update.php" method="POST">
			<input type='hidden' value='<?php echo $seldbname?>' name='seldbname'>
			<input type="hidden" name="tablename" value="<?echo "$tablename";?>">
			<input type='hidden' value='<?php echo $anticollisionwell_id?>' name='cid'>
			<table width="996px" >
				<tr><td>
					<table><tr><td width="200px">Line color:</td><td><input name="color" value="<?php echo $color?>" type='text'></td></tr></table></td>
					<td><table><tr><td width="200px">Proposed Dir:</td><td><input name="propdir" value="<?php echo $propdir?>" type='text'></td></tr></table></td></tr>
				<tr><td>
					<table><tr><td width="200px">Surface Location:</td><td></td></tr>
					<tr><td width="200px">Easting(x):</td><td><input name='eastingsl' value="<?php echo $eastingsl?>" type='text'></td></tr>
					<tr><td width="200px">Northing(Y):</td><td><input name='northingsl' value="<?php echo $northingsl?>" type='text'></td></tr>
				</table></td>
				<td>
				<table><tr><td width="200px">Elevation</td><td></td></tr>
					<tr><td width="200px">Ground:</td><td><input  name='ground' value="<?php echo $ground?>" type='text'></td></tr>
					<tr><td width="200px">RKB:</td><td><input  name='rkb' value="<?php echo $rkb?>" type='text'></td></tr>
				</table>
				</td>
			<td>
			<table>
						<tr><td width="200px">Correction:</td><td> <select name="correction"><option value='True North' <?php if($correction=='True North')echo 'selected'?>>True North</option><option value="Grid" <?php if($correction=='Grid')echo 'selected'?>>Grid</option></select></td></tr>
						<tr><td width="200px">Coordinate System:</td><td><select name="coor_system"><option value='Polar' <?php if($coor_system=='Polar')echo 'selected'?>>Polar</option><option value='Cartesian' <?php if($coor_system=='Cartesian')echo 'selected'?>>Cartesian</option></select></td></tr>
					</table>
			</td></tr>
			<tr><td colspan='8' style='text-align:right'><input type='submit' value='save'></td></tr>
			</table>
			</form>
		</td>
		</tr>
		<TR>
		<td>
		 	<table>
		 		<FORM ACTION="anticollision_addsurvey.php" METHOD="post">
		 		
		<TD style='width:100px;height:45px;vertical-align:bottom'>
			<TABLE style='width:180px;border-spacing:0;padding:2;'>
			<TR>
			<TH class='surveys'>MD</TH>
			<TH class='surveys'>Inc</TH>
			<TH class='surveys' style='border-right: thin solid black;'>Azm</TH>
			</TR>
			<TR>
			<TD class='surveys'>
				<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
				<input type="hidden" name="tablename" value="<?echo "$tablename";?>">
				<input type="hidden" name="cid" value="<?echo "$anticollisionwell_id";?>">
				<INPUT class='surveys' TYPE="text" VALUE="0.0" NAME="md" SIZE="6">
			</TD>
			<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="0.0" NAME="inc" SIZE="6">
			</TD>
			<TD class='surveys' style='border-right: thin solid black;'>
				<INPUT class='surveys' TYPE="text" VALUE="0.0" NAME="azm" SIZE="6">
			</TD>
			</TR>
			</TABLE>
		</TD>
		<TD style='vertical-align:bottom;padding:2;'>
			<INPUT TYPE="submit" VALUE="Add Survey" NAME="AddNew">
			
		</TD>
		</FORM>
		 	</table>
		</td></tr>
		<tr><td>
		<FORM ACTION="anticollision_csvimport.php" METHOD="post" enctype="multipart/form-data">
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type="hidden" name="tablename" value="<?echo "$tablename";?>">
			<input type="hidden" name="cid" value="<?echo "$anticollisionwell_id";?>">
			<input name="userfile" type="file" size="85" placeHolder="Select CSV Survey Import File">
			<INPUT TYPE="submit" VALUE="CSV Import" NAME="CSVImport">
		</form>
		</td></tr>
		</TABLE>
		<?}?>
	</td>
	</tr>
	<tr>
	<td>
		<?if($anticollisionwell_id===false){?>
		<table class='surveys'  style="width:996px;">
		<TR>
		<td>
			Select an Anti-Collision Well or Create a new one
		</td>
		</tr>
		</TABLE>
		<?} else {?>
			<table class='surveys' style="width:996px">
		<TR>
		<td style='text-align: right;' colspan='12'>
			<FORM ID="delsvys" NAME="delsvys" METHOD="post">
			<INPUT TYPE="hidden" NAME="seldbname" VALUE="<?echo "$seldbname";?>">
			<input type="hidden" name="tablename" value="<?echo "$tablename";?>">
			<input type='hidden' value='<?php echo $anticollisionwell_id?>' name='cid'>
			<INPUT TYPE="hidden" NAME="sids" VALUE="">
			</FORM>
			<INPUT TYPE="submit" VALUE="Select All" ONCLICK="OnSetChecks()">
			<INPUT TYPE="submit" VALUE="Select None" ONCLICK="OnClearChecks()">
			<INPUT TYPE="submit" VALUE="Delete Selected" ONCLICK="OnDelSurveys()">
		</td>
		</TR>
		<TR> 
		<TH class='surveys'>#</TH>
		<TH class='surveys'>MD</TH>
		<TH class='surveys'>Inc</TH>
		<TH class='surveys'>Azm</TH>
		<TH class='surveys'>TVD</TH>
		<TH class='surveys'>VS</TH>
		<TH class='surveys'>NS</TH>
		<TH class='surveys'>EW</TH>
		<TH class='surveys'>CD</TH>
		<TH class='surveys'>CA</TH>
		<TH class='surveys'>DL</TH>
		<TH class='surveys'>Del</TH>
		</TR>
		<?php
			$sql = "select count(*) as cnt from $tablename";
			$db->DoQuery($sql);
			$row = $db->FetchRow();
			$i = $row['cnt']-1;
			?><INPUT TYPE="hidden" NAME="numsvys" VALUE="<?echo  $row['cnt'];?>" ID="numsvys"><?
			$sql = "select * from $tablename order by md desc";
			$db->DoQuery($sql);
			while($row = $db->FetchRow()){
				$id=$db->FetchField("id");
				$plan=0;
				$md=sprintf("%.2f", $db->FetchField("md"));
				$inc=sprintf("%.2f", $db->FetchField("inc"));
				$azmraw = $db->FetchField("azm");
				$caraw = $db->FetchField("ca");
				$cdraw = $db->FetchField("cd");
				$azm=sprintf("%.2f", $db->FetchField("azm"));
				$tvd=sprintf("%.2f", $db->FetchField("tvd"));
				$vs=sprintf("%.2f", $db->FetchField("vs"));
				$nsraw = $db->FetchField("ns");
				$ns=sprintf("%.2f", $db->FetchField("ns"));
				$ewraw = $db->FetchField("ew");
				$ew=sprintf("%.2f", $db->FetchField("ew"));
				$cd=sprintf("%.2f", $db->FetchField("cd"));
				$ca=sprintf("%.2f", $db->FetchField("ca"));
				$dl=sprintf("%.2f", $db->FetchField("dl"));
				
			if($i%4<=1) $classstr="<TD class='gridro2'>";
			else $classstr="<TD class='gridro'>";
			?>
			<TR> 
			<FORM ACTION="anticollision_changsurvey.php" NAME="F<?echo $id?>" METHOD="post">
			<INPUT TYPE="hidden" VALUE="<?echo $id;?>" NAME="id">
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type="hidden" name="tablename" value="<?echo "$tablename";?>">
			<input type='hidden' value='<?php echo $anticollisionwell_id?>' name='cid'>
			<TD class='surveys'>
			<?echo $i?>
			</td>
			<? if($i==0) { ?>
				<INPUT TYPE="hidden" VALUE="<?echo $plan;?>" NAME="plan">
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $md;?>" NAME="md" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $inc;?>" NAME="inc" SIZE="4" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $azm;?>" NAME="azm" SIZE="4" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $tvd;?>" NAME="tvd" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $vs;?>" NAME="vs" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $ns;?>" NAME="ns" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $ew;?>" NAME="ew" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<?
				echo "$classstr $cd</TD>";
				echo "$classstr $ca</TD>";
				echo "$classstr $dl</TD>";
			}
			else {?>
				<INPUT TYPE="hidden" VALUE="<?echo $plan;?>" NAME="plan">
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $md;?>" NAME="md" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $inc;?>" NAME="inc" SIZE="4" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $azm;?>" NAME="azm" SIZE="4" ONCHANGE="OnSubmit(this.form)"></TD>
				<?
				echo "$classstr $tvd</TD>";
				echo "$classstr $vs</TD>";
				echo "$classstr $ns</TD>";
				echo "$classstr $ew</TD>";
				echo "$classstr $cd</TD>";
				echo "$classstr $ca</TD>";
				echo "$classstr $dl</TD>";
			}
				
			?>
			</FORM>
			<FORM id="f<?echo $i;?>" NAME="f<?echo $i;?>" METHOD="post">
			<TD class='surveys'>
				<INPUT TYPE="hidden" NAME="seldbname" VALUE="<?echo "$seldbname";?>">
				<INPUT TYPE="hidden" VALUE="ASC" NAME="sortdir">
				<INPUT TYPE="hidden" NAME="id" VALUE="<?echo $id;?>">
				<INPUT class='surveys' TYPE="checkbox" VALUE="0" NAME="del">
			</TD>
			</FORM>
			</TR>
				
			<?$i--;
			}
		?>
		</table>
		
		<?}?>
	</td>
	</tr>
<tr>
<td>
	<center><small><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></small></center>
</td>
</tr>
	</table>
</td>
</tr>
</table>
</BODY>
</HTML>
<?php 
$db->CloseDb();
?>
