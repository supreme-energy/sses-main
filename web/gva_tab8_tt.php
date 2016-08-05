<?php
// Writen by John Arnold (from bits and pieces of Richard's old code)

$md=0; $inc=0; $azm=0;
$recalc = (isset($_REQUEST['recalc']) ? $_REQUEST['recalc'] : '');
$numprojs = 0;

if($autoposdec > 0)
{
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$sql = "select (tot-tvd) as bprjtops from surveys where plan = 1;";
	$db->DoQuery($sql);
	$bprjtpos_r=$db->FetchRow();
	$sval = $bprjtpos_r['bprjtops']; 
	if($sval>0){
		$svalsign='positive';
	} else{
		$svalsign='negative';
	}
	$decval= $autoposdec;
	if($svalsign=='negative') $decval=$decval*-1;
	$sql = "select * from projections order by md";
	$db->DoQuery($sql);
	while($r1 = $db->FetchRow()){
		$sval = $sval - $decval;
		if($db->FetchField('method')==8){			
			$rowid = $db->FetchField('id');
			$data = $db->FetchField('data');
			$split = explode(',',$data);
			if($svalsign=='positive'){
				if($sval < 0) $sval = 0;
			} else{
				if($sval > 0) $sval = 0;
			}
			$split[1]=$sval;
			$ndata = implode(',',$split);
			$sql = "update projections set data='$ndata' where id=$rowid";
			$db2->DoQuery($sql);
		}
	}
	$db2->CloseDb();
}
$jsurvs = '';
if($sgta_off){
	$jsurvs= '  --justsurveys';
}
// commented because it already runs in the parent module
exec("./sses_gva -d $seldbname ");
exec("./sses_cc -d $seldbname");
// if("$recalc"!="")
	// exec("./sses_gva -d $seldbname --justsurveys");
exec("./sses_cc -d $seldbname -p");
exec ("./sses_af -d $seldbname");

include 'readsurveys.inc.php';
include 'gva_tab3_funct.php';

?>
<style>
#the-target-tracker-div {
	margin:10px;
}
</style>

<input type='hidden' id='seldbn' value='<?php echo $seldbname ?>'>

<div id='the-target-tracker-div'>

<!-- start of the target tracker buttons div -->

<div>
	<div class='buttons' style='float:left;font-size:8pt;margin:2px'>
		<input type='button' name='choice' value='PDF Report'
			onclick="window.open('outputpicker.php?seldbname=<?php
			echo $seldbname ?>&title=Survey%20Report&program=surveypdf.php&filename=/tmp/<?php
			echo $seldbname ?>.surveys.pdf&showxy=<?php
			echo $showxy ?>','popuppage','width=200,height=220,left=500');">
		<input type='button' name='choice' value='Text Report'
			onclick="window.open('outputpicker.php?seldbname=<?php
			echo $seldbname ?>&title=Survey%20Report&program=surveyprint.php&filename=/tmp/<?php
			echo $seldbname ?>.surveys.pdf&showxy=<?php
			echo $showxy ?>','popuppage','width=200,height=220,left=500');">
		<input type='button' value='Export to CSV' OnClick='OnSurveyCSV()'>
		<input type='button' name='choice' value='Plot Surveys'
			onclick="window.open('splotconfig.php?seldbname=<?php
			echo $seldbname ?>&title=Survey%20Plot','popuppage','width=450,height=275,left=250')">
		<input type='button' name='choice' value='Auto Importer'
			onclick="window.open('autosurveyloader.php?seldbname=<?php
			echo $seldbname ?>','_blank','width=500,height=280,left=250,location=no,menubar=no,resizable=no,status:no,toolbar=no')">
		<input type='button' name='choice' value='AntiCollision'
			onclick="window.open('anticollisionwells.php?seldbname=<?php echo $seldbname ?>','_blank',
			'width=1300,height=700,top=50,left=150,location=no,menubar=no,resizable=yes,status:no,toolbar=no');">
		<input type='button' name='choice' value='Slide Sheet'
			onclick="window.open('rotslide.php?seldbname=<?php echo $seldbname ?>','_blank',
			'width=900,height=600,top=50,left=150,location=no,menubar=no,resizable=yes,status:no,toolbar=no,scrollbars=yes')">
		<input type='button' name='choice' value='Real Time'
			onclick="window.open('realtime.php?seldbname=<?php
			echo $seldbname ?>','_blank','width=450,height=750,left=250,location=no,menubar=no,resizable=no,status:no,toolbar=no')">	
	</div>

	<div class='buttons' style='float:left;font-size:8pt;margin:2px'>
		<form action='ttupdate.php' method='post'>
		<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
		<input type="hidden" name="id" value="<?echo "$infotableid";?>">
		<input type="hidden" name="ret" value="<?echo "$ret";?>">
		Proposed Direction: <input type="text" size="5" name="propazm" value="<? echo "$propazm"?>">
		TCL: <input type="text" size="5" name="tot" value="<? echo "$plantot"?>">
		Projection Dip: <input type="text" size="5" name="projdip" value="<? echo "$projdip"?>">
		<input style='display:none' type="text" size="5" name="bot" value="<? echo "$planbot"?>">
		<input type='submit' value='Save Changes' onclick="return ray.ajax()">
		</form>
	</div>

	<div style='clear:both'></div>

	<div class='buttons' style='float:left;font-size:8pt;margin:2px'>
		<div style='font-weight:bolder'>
			<div style='width:50px;float:left;text-align:center'>Depth</div>
			<div style='width:50px;float:left;text-align:center'>Inc</div>
			<div style='width:50px;float:left;text-align:center'>Azimuth</div>
			<div style='clear:both'></div>
		</div>
		<div>
			<div style='float:left'>
				<form method='post' action='surveyadd.php'>
				<input type='hidden' name='seldbname' value='<?php echo $seldbname ?>'>
				<input type='hidden' name='ret' value='<?php echo $ret ?>'>
				<div style='width:50px;float:left;text-align:center'>
					<input type='text' value='0' name='md' size='5' />
				</div>
				<div style='width:50px;float:left;text-align:center'>
					<input type='text' value='<?php echo $inc ?>' name='inc' size='5' />
				</div>
				<div style='width:50px;float:left;text-align:center'>
					<input type='text' value='<?php echo $azm ?>' name='azm' size='5' />
				</div>
				<div style='float:left'>
					<input type='submit' value='Add Survey' />
				</div>
				<div style='clear:both'></div>
				</form>
			</div>
			<div style='float:left'>
				<form method='post'>
				<input type='hidden' name='seldbname' value='<?php echo $seldbname ?>' />
				<input type='hidden' name='returnto' value='gva_tab8.php'>
				<input type='submit' value='Import Surveys From CSV' onclick='OnImport(this.form)' />
				</form>
			</div>
			<div style='clear:both'></div>
		</div>
	</div>

	<div class='buttons' style='float:left;font-size:8pt;margin:2px'>
		<div style='text-align:center;font-weight:bolder'>Projection Calculator</div>
		<div style='float:left'>
			<form action='projws.php' target='_blank' method='post'>
			<input type='hidden' name='seldbname' value='<?php echo $seldbname ?>'>
			<input type='hidden' name='ret' value='gva_tab8.php'>
			<input type='hidden' name='project' value='bit'>
			<input type='hidden' name='propazm' value='<?php echo $propazm ?>'>
			<input type='submit' value='Bit Projection' onclick='projws(this.form)' <?php
				if($svy_total < 2) echo "disabled='true'"; ?> />
			</form>
		</div>
		<div style='float:left'>
			<form action='oujiaws.php' target='_blank' method='post'>
			<input type='hidden' name='seldbname' value='<?php echo $seldbname ?>'>
			<input type='hidden' name='ret' value='gva_tab8.php'>
			<input type='hidden' name='project' value='ahead'>
			<input type='hidden' name='propazm' value='<?php echo $propazm ?>'>
			<input type='submit' value='Ouija' onclick='oujiaws(this.form)' <?php
				if($svy_total < 2) echo "disabled='true'" ?> />
			</form>
		</div>
		<div style='float:left'>
			<FORM action='projws.php' target='_blank' method='post'>
			<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
			<input type='hidden' name='ret' value='gva_tab8.php'>
			<input type='hidden' name='project' value='ahead'>
			<input type="hidden" name="propazm" value="<? echo "$propazm"?>">
			<input type="submit" value="Add Projection" onclick="projws(this.form)" <?php
				if($svy_total < 2) echo "disabled='true'"; ?> />
			</FORM>
		</div>
		<div style='float:left'>
			<FORM action='fixedlanding.php' target='_blank' method='get'>
			<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
			<input type='hidden' name='ret' value='gva_tab8.php'>
			<input type="submit" value="Fixed Landing" onclick = "fixedlanding(this.form)">
			</FORM>
		</div>
		<div style='clear:both'></div>
	</div>

	<div class='buttons' style='float:left;font-size:8pt;margin:2px'>
		<div style='float:left'>
		<form class='raw' action='gva_tab8.php' method='get' onsubmit='return ray.ajax()'>
		<input type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<input type='hidden' name='sortdir' value='<?echo $revsortdir?>'>
		<input TYPE="submit" VALUE="Reverse Sort">
		</form>
		</div>

		<div style='float:left'>
		<form class='raw' action='gva_tab8.php' method='get' onsubmit='return ray.ajax()'>
		<input type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<input type='hidden' name='noshowxy' value='<?echo $noshowxy?>'>
		<input TYPE="submit" VALUE="<?php if($showxy==1) echo 'Show CA/CD'; else echo 'Show X/Y'; ?>">
		</form>
		</div>

		<div style='float:left'>
		<form ID="avgdipform" NAME="avgdipform" METHOD="get">
		<input type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<input type='hidden' name='dip' value='<?echo $projdip?>'>
		<input type='hidden' name='ret' value='gva_tab8.php'>
		<input TYPE="submit" VALUE="Average Dip" onclick='return OnAvgDip()'>
		</form>
		</div>

		<div style='float:left'>
		<form ID="recalc" NAME="recalc" METHOD="post">
		<input TYPE="hidden" NAME="seldbname" VALUE="<?echo "$seldbname";?>">
		<input type='hidden' name='sortdir' value='<?echo $sortdir?>'>
		<input type='hidden' name='recalc' value='true'>
		<input TYPE="submit" VALUE="Recalculate Postions">
		</form>
		</div>

		<div style='clear:both'></div>
	</div>

	<div class='buttons' style='float:left;font-size:8pt;margin:2px'>
		<div style='float:left;margin:0 2px'>
		<FORM ID="delsvys" NAME="delsvys" METHOD="post">
		<INPUT TYPE="hidden" NAME="seldbname" VALUE="<?echo "$seldbname";?>">
		<input type='hidden' name='sortdir' value='<?echo $sortdir?>'>
		<INPUT TYPE="hidden" NAME="sids" VALUE="">
		<input type='hidden' name='deccl' value='f'>
		<input type='hidden' name='deccl_m' value='0'>
		<input type='hidden' name='returnto' value='gva_tab8.php'>
		</FORM>
		</div>

		<div style='float:left'>
		<INPUT TYPE="submit" VALUE="Select All" ONCLICK="OnSetChecks()">
		</div>

		<div style='float:left'>
		<INPUT TYPE="submit" VALUE="Select None" ONCLICK="OnClearChecks()">
		</div>

		<div style='float:left'>
		<INPUT TYPE="submit" VALUE="Delete Selected" ONCLICK="OnDelSurveys()">
		</div>

		<div style='clear:both'></div>
	</div>

	<div style='clear:both'></div>

</div> <!-- end of the target tracker buttons div -->

<table class='surveys'>
<tr> 
<th colspan='12' style='text-align:center'>Survey Data</th>
<th colspan='4' style='text-align:center'>Target Tracker Section</th>
</tr>
<tr> 
<th class='surveys'>Svy</th>
<th class='surveys'>Depth</th>
<th class='surveys'>Inc</th>
<th class='surveys'>Azm</th>
<th class='surveys'>TVD</th>
<th class='surveys'>VS</th>
<th class='surveys'>NS</th>
<th class='surveys'>EW</th>
<?php
if($showxy == 1)
{
	echo "<th class='surveys'>Northing</th>";
	echo "<th class='surveys'>Easting</th>";
}
else
{
	echo "<th class='surveys'>CD</th>";
	echo "<th class='surveys'>CA</th>";
}
?>
<th class='surveys'>DL</th>
<th class='surveys'>CL</th>
<th class='surveys'>TF</th>
<th class='rot'>TCL</th>
<th class='rot'>Pos-TCL</th>
<th class='rot'>TOT</th>
<th class='rot'>BOT</th>
<th class='rot'>Dip</th>
<th class='rot'>Fault</th>
<th class='rot'>Del</th>
</tr>
<?
if($surveysort == 'DESC') PrintProjections();
PrintSurveys();
if($surveysort == 'ASC') PrintProjections();
?>
<tr> 
<th class='surveys'>Svy</th>
<th class='surveys'>Depth</th>
<th class='surveys'>Inc</th>
<th class='surveys'>Azm</th>
<th class='surveys'>TVD</th>
<th class='surveys'>VS</th>
<th class='surveys'>NS</th>
<th class='surveys'>EW</th>
<?php
if($showxy==1)
{
	echo "<th class='surveys'>Northing</th>";
	echo "<th class='surveys'>Easting</th>";
}
else{

	echo "<th class='surveys'>CD</th>";
	echo "<th class='surveys'>CA</th>";
}
?>
<th class='surveys'>DL</th>
<th class='surveys'>CL</th>
<th class='surveys'>TF</th>
<th class='rot'>TCL</th>
<th class='rot'>Pos-TCL</th>
<th class='rot'>TOT</th>
<th class='rot'>BOT</th>
<th class='rot'>Dip</th>
<th class='rot'>Fault</th>
<th class='rot'>Del</th>
</tr>
</table>
</div>

<INPUT TYPE="hidden" NAME="surveysort" VALUE="<?echo $surveysort;?>" ID="surveysort">
<INPUT TYPE="hidden" NAME="numsvys" VALUE="<?echo $svy_total;?>" ID="numsvys">
<INPUT TYPE="hidden" NAME="numprojs" VALUE="<?echo $numprojs;?>" ID="numprojs">
<INPUT TYPE="hidden" NAME="svy_cnt" VALUE="<?echo $svy_cnt;?>" ID="svy_cnt">
<INPUT TYPE="hidden" NAME="svy_total" VALUE="<?echo $svy_total;?>" ID="svy_total">
