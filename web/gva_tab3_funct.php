<?php
// Built by: John Arnold
// Last updated: 5/7/2015

function PrintProjections()
{
	global $seldbname, $ret, $survey_northing, $survey_easting;
	global $surveysort, $svy_total, $numprojs,$sgta_off;
	global $lastmd, $showxy;
	$db3=new dbio($seldbname);
	$db3->OpenDb();
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$db2->DoQuery("select * from addforms;");
	$totid =null;
	$botid = null;
	while($db2->FetchRow()){
		
		if(trim($db2->FetchField('label'))=='TOT'){
			$totid = $db2->FetchField('id');
		}
		if(trim($db2->FetchField('label'))=='BOT'){
			$botid = $db2->FetchField('id');
		}
	}

	$db2->DoQuery("SELECT * FROM projections ORDER BY md $surveysort");
	$numprojs=$db2->FetchNumRows(); 
	for ($i=0; $i<$numprojs; $i++) {
		$db2->FetchRow();
		$id=$db2->FetchField("id");
		$md =  $db2->FetchField("md");
		$tot='NF';
		$bot='NF';
		if($totid){
			$query = "select tot from addformsdata where projid=$id and infoid=$totid;"; 
			$db3->DoQuery($query);
			$db3->FetchRow();
			$tot =sprintf("%.2f", $db3->FetchField("tot"));
		}
		if($botid){
			$query = "select tot from addformsdata where projid=$id and infoid=$botid;";
			$db3->DoQuery($query);
			$db3->FetchRow();
			$bot =sprintf("%.2f", $db3->FetchField("tot"));
		}
		
		$meth=sprintf("%.2f", $db2->FetchField("method"));
		$md=sprintf("%.2f", $db2->FetchField("md"));
		$inc=sprintf("%.2f", $db2->FetchField("inc"));
		$azm=sprintf("%.2f", $db2->FetchField("azm"));
		$tvd=sprintf("%.2f", $db2->FetchField("tvd"));
		$ns=sprintf("%.2f", $db2->FetchField("ns"));
		$ew=sprintf("%.2f", $db2->FetchField("ew"));
		$vs=sprintf("%.2f", $db2->FetchField("vs"));
		$ca=sprintf("%.2f", $db2->FetchField("ca"));
		$cd=sprintf("%.2f", $db2->FetchField("cd"));
		$dl=sprintf("%.2f", $db2->FetchField("dl"));
		$cl=sprintf("%.2f", $db2->FetchField("cl"));
		$tcl = sprintf("%.2f", $db2->FetchField("tot"));
		$dip=sprintf("%.2f", $db2->FetchField("dip"));
		$fault=sprintf("%.2f", $db2->FetchField("fault"));
		$hide=sprintf("%d", $db2->FetchField("hide"));
		$tf = $db2->FetchField("tf");
		if(!$tf){
			$tf='-';
		}
		$pnum=$i+1;
		if($surveysort=="DESC") 	$pnum = $numprojs-$i;
		if($showxy==1) {
			$cd=sprintf("%.0f", $survey_northing+$ns);
			$ca=sprintf("%.0f", $survey_easting+$ew);
		}
		$tdstring=" onclick='showMethod(event,$i)' onmouseover='showline($i)' onmouseout='noshowline()' ";
		$ptype = $db2->FetchField("ptype");
		$disppa = strtoupper($ptype).$pnum;
		echo "<TR> 
		<TD class='surveys'>
			<FORM ACTION='projws.php' METHOD='post'>
			<INPUT TYPE='hidden' NAME='seldbname' VALUE='$seldbname'>
			<INPUT TYPE='hidden' NAME='currid' VALUE='$id'>
			<INPUT TYPE='hidden' NAME='project' VALUE='ahead'>
			<INPUT TYPE='hidden' NAME='ret' VALUE='$ret'>
			<INPUT CLASS='edit' TYPE='submit' VALUE='$disppa' \
				onclick='projws(this.form)' onmouseover='showline($i)' onmouseout='noshowline()'>
			</FORM>
		</TD>\n";
//		echo "<INPUT TYPE='hidden' id='gridmeth$i' VALUE='$meth'>\n";
		echo "		<TD id='gridmd$i' $tdstring class='gridproj gridmdcl'>$md</TD>
		<TD id='gridinc$i' $tdstring class='gridproj gridmdcl'>$inc</TD>
		<TD id='gridazm$i' $tdstring class='gridproj gridmdcl'>$azm</TD>
		<TD id='gridtvd$i' $tdstring class='gridproj gridmdcl'>$tvd</TD>
		<TD id='gridvs$i' $tdstring class='gridproj gridmdcl'>$vs</TD>
		<TD id='gridns$i' $tdstring class='gridproj gridmdcl'>$ns</TD>
		<TD id='gridew$i' $tdstring class='gridproj gridmdcl'>$ew</TD>
		<TD id='gridcd$i' $tdstring class='gridproj gridmdcl'>$cd</TD>
		<TD id='gridca$i' $tdstring class='gridproj gridmdcl'>$ca</TD>
		<TD id='griddl$i' $tdstring class='gridproj gridmdcl'>" . ($ptype == 'sld' ? '-' : $dl) . "</TD>\n";
		echo "		<TD id='gridcl$i' $tdstring class='gridproj gridmdcl'>$cl</TD>\n";
		echo "		<TD id='gridcl$i' $tdstring class='gridproj gridmdcl'>$tf</TD>\n";
		echo "		<TD id='gridtot$i' $tdstring class='gridproj gridtclbot'>$tcl</TD>\n";
		printf("		<TD id='gridtpos$i' $tdstring class='gridproj gridtclbot'>%.2f</TD>\n", $tcl-$tvd);
		echo "		<TD id='gridbot$i' $tdstring class='gridproj gridtclbot'>$tot</TD>\n";
		echo "		<TD id='gridbot$i' $tdstring class='gridproj gridtclbot'>$bot</TD>\n";

		echo "		<TD id='griddip$i' $tdstring class='gridproj gridtclbot'>$dip</TD>\n";
		echo "		<TD id='gridfault$i' class='gridproj gridtclbot'>$fault</TD>\n";
		if($surveysort=="ASC") $pfc=$svy_total+$i;
		else $pfc=$i;
		echo "		<TD class='surveys gridtclbot' style='text-align:center;vertical-align:middle'>
			<FORM id='f$pfc' NAME='f$pfc' METHOD='post'>
			<INPUT TYPE='hidden' NAME='seldbname' VALUE='$seldbname'>
			<INPUT TYPE='hidden' NAME='id' VALUE='p$id'>
			<INPUT TYPE='checkbox' VALUE='0' NAME='del' >
			</FORM>
		</TD>
</TR>\n";
		$lastmd=$md;
	}
	$db2->CloseDb();
	$db3->CloseDb();
}

function PrintSurveys()
{
	global $seldbname, $survey_northing, $survey_easting;
	global $surveysort, $svy_total, $numprojs;
	global $lastmd, $showxy;
	global $svy_cnt, $svy_total, $svy_id, $svy_plan, $svy_md, $svy_inc, $svy_azm, $svy_tvd,$svy_isghost;
	global $svy_vs, $svy_ns, $svy_ew, $svy_ca, $svy_cd, $svy_dl, $svy_cl, $svy_tot,$svy_tcl;
	global $svy_bot, $svy_dip, $svy_fault,$sgta_off;
	$curr_svy_num=0;
	$high_svy_num=0;
	$high_svy_cl=0;
	for ($i=0; $i<$svy_total; $i++) {
		$id=$svy_id[$i];
		$md=sprintf("%.2f", $svy_md[$i]);
		$inc=sprintf("%.2f", $svy_inc[$i]);
		$azm=sprintf("%.2f", $svy_azm[$i]);
		$tvd=$svy_tvd[$i];
		$ns=sprintf("%.2f", $svy_ns[$i]);
		$ew=sprintf("%.2f", $svy_ew[$i]);
		$vs=sprintf("%.2f", $svy_vs[$i]);
		$ca=sprintf("%.2f", $svy_ca[$i]);
		$cd=sprintf("%.2f", $svy_cd[$i]);
		$dl=sprintf("%.2f", $svy_dl[$i]);
		$cl=sprintf("%.2f", $svy_cl[$i]);
		$tcl = $svy_tcl[$i];
		$tot=$svy_tot[$i];
		$bot=$svy_bot[$i];
		$isghost= $svy_isghost[$i];
		$dip=sprintf("%.2f", $svy_dip[$i]);
		$fault=sprintf("%.2f", $svy_fault[$i]);
		$plan=sprintf("%d", $svy_plan[$i]);
		if($showxy==1) {
			$cd=sprintf("%.0f", $survey_northing+$ns);
			$ca=sprintf("%.0f", $survey_easting+$ew);
		}
		$bgcolorchange="";
		if($isghost==1){
			$bgcolorchange="background-color:white;";
		}
		if(!$sgta_off){
			$dipstr=$dip;
			$faultstr=$fault;
		} else {
			$dipstr="<input type='text' size='3' value='$dip' id ='inputdip_$id' onchange='save_dip(this)'>";
			$faultstr="<input type='text' size='3' value='$fault' id='inputfault_$id' onchange='save_fault(this)'>";
		}
		echo "<TR style=\"$bgcolorchange\">\n<FORM METHOD='post'>\n";
		echo "<INPUT TYPE='hidden' VALUE='$dip' NAME='dipdip' ID='dipdip$i'>\n";
		echo "<INPUT TYPE='hidden' VALUE='$id' NAME='id' ID='$id'>\n";
		echo "<input type='hidden' name='seldbname' value='$seldbname'>\n";
		$si=$i+$numprojs;
		if($plan==0) {
			if($i%4<=1) echo "<TD class='gridro2 gridmdcl'>\n";
			else echo "<TD class='gridro gridmdcl'>\n";
			if(strtoupper($surveysort)=="ASC") $cur_svy_num= $i;
			else $cur_svy_num = $svy_total-$i-1;
			echo $cur_svy_num;
			if($high_svy_num < $cur_svy_num){
				$high_svy_num = $cur_svy_num;
				$high_svy_cl  = $cl;
			}
			
			echo "</TD>\n<TD class='grid gridmdcl'>";
			echo "<INPUT TYPE='text' class='surveys' VALUE='$md' NAME='md' SIZE='7' ONCHANGE='OnSurvey(this.form)'>";
			echo "</TD>\n<TD class='grid gridmdcl'>";
			echo "<INPUT TYPE='text' class='surveys' VALUE='$inc' NAME='inc' SIZE='5' ONCHANGE='OnSurvey(this.form)'>";
			echo "</TD>\n<TD class='grid gridmdcl'>";
			echo "<INPUT TYPE='text' class='surveys' VALUE='$azm' NAME='azm' SIZE='5' ONCHANGE='OnSurvey(this.form)'>";
			echo "</TD>\n";
			if(($i == 0 and $surveysort == "ASC") || ($i >= ($svy_total - 1) and $surveysort == "DESC"))
			{ 
				echo "<TD class='grid gridmdcl'  style=\"$bgcolorchange\">";
				echo "<INPUT TYPE='text' class='surveys' VALUE='";
				printf("%.2f", $tvd);
				echo "' NAME='tvd' SIZE='7' ONCHANGE='OnSurvey(this.form)'>";
				echo "</TD>\n<TD class='grid gridmdcl'  style=\"$bgcolorchange\">";
				echo "<INPUT TYPE='text' class='surveys' VALUE='$vs' NAME='vs' SIZE='5' ONCHANGE='OnSurvey(this.form)'>";
				echo "</TD>\n<TD class='grid gridmdcl'  style=\"$bgcolorchange\">";
				echo "<INPUT TYPE='text' class='surveys' VALUE='$ns' NAME='ns' SIZE='5' ONCHANGE='OnSurvey(this.form)'>";
				echo "</TD>\n<TD class='grid gridmdcl'>";
				echo "<INPUT TYPE='text' class='surveys' VALUE='$ew' NAME='ew' SIZE='5' ONCHANGE='OnSurvey(this.form)'>";
				echo "</TD>\n";
				if($i%4<=1) $tdcls="<TD class='gridro2 gridmdcl'  style=\"$bgcolorchange\">";
				else $tdcls="<TD class='gridro gridmdcl'  style=\"$bgcolorchange\">";
				echo "$tdcls $cd</TD>\n";
				echo "$tdcls $ca</TD>\n";
				echo "$tdcls - </TD>\n";
				echo "$tdcls $cl</TD>\n";
				echo "$tdcls -</TD>\n";
			} else {
				if($i%4<=1) $tdcls="<TD class='gridro2 gridmdcl'  style=\"$bgcolorchange\">";
				else $tdcls="<TD class='gridro gridmdcl'  style=\"$bgcolorchange\">";
				echo "$tdcls";printf("%.2f</TD>\n", $tvd);
				echo "$tdcls $vs</TD>\n";
				echo "$tdcls $ns</TD>\n";
				echo "$tdcls $ew</TD>\n";
				echo "$tdcls $cd</TD>\n";
				echo "$tdcls $ca</TD>\n";
				echo "$tdcls $dl</TD>\n";
				echo "$tdcls $cl</TD>\n";
				echo "$tdcls -</TD>\n";
			}
			if($i%4<=1) $tdcls="<TD class='gridrot2 gridtclbot'  style=\"$bgcolorchange\">";
			else $tdcls="<TD class='gridrot gridtclbot'  style=\"$bgcolorchange\">";
			
			echo $tdcls; printf("%.2f</TD>\n", $tcl);
			echo $tdcls; printf("%.2f</TD>\n", $tcl-$tvd);
			echo "$tdcls $tot</TD>\n";
			echo "$tdcls $bot</TD>\n";
			echo "$tdcls $dipstr</TD>\n";
			echo "$tdcls $faultstr</TD>\n";
			
		}
		else {	// plan==1
			$label="BPrj";
			if($i%4<=1) $tdcls="<TD class='gridrot2 gridtclbot'  style=\"$bgcolorchange\">";
			else $tdcls="<TD class='gridrot gridtclbot'  style=\"$bgcolorchange\">";
			$tdcls="<TD class='gridproj gridtclbot'  style=\"$bgcolorchange\">";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\" id=\"$id\">$label</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">$md</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">$inc</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">$azm</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">";printf("%.2f</TD>\n", $tvd);
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">$vs</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">$ns</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">$ew</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">$cd</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">$ca</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">$dl</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">$cl</TD>\n";
			echo "<TD class='gridproj gridmdcl'  style=\"$bgcolorchange\">-</TD>\n";
			echo $tdcls; printf("%.2f</TD>\n", $tcl);
			printf("$tdcls%.2f</TD>\n", $tcl-$tvd);
			echo "$tdcls $tot</TD>\n";
			echo "$tdcls $bot</TD>\n";
			echo "$tdcls $dipstr</TD>\n";
			echo "$tdcls $faultstr</TD>\n";
			
		}
		echo "</FORM>\n";
		if($surveysort=="DESC") $pfc=$numprojs+$i;
		else $pfc=$i;
		echo "<TD class='surveys gridtclbot' style='text-align:center;vertical-align:middle;$bgcolorchange'>\n";
		echo "	<FORM id='f$pfc' NAME='f$pfc' METHOD='post'>";
		if(!isset($cur_svy_num)) $cur_svy_num = '';
		echo "	<input type='hidden' value='$cur_svy_num' name='num' id='num_$id'>\n";
		echo "	<INPUT TYPE='hidden' NAME='seldbname' VALUE='$seldbname'>\n";
		echo "	<INPUT TYPE='hidden' NAME='id' VALUE='$id'>\n";
		echo "	<INPUT TYPE='checkbox' VALUE='0' NAME='del'>\n";
		echo "	</FORM>\n</TD>\n</TR>\n";
		$lastmd=$md;
	}
	echo "<INPUT TYPE='hidden' NAME='high_num' id='high_num' VALUE='$high_svy_num'>\n";
	echo "<INPUT TYPE='hidden' NAME='high_cl'  id='high_cl' VALUE='$high_svy_cl'>\n";
}
?>
