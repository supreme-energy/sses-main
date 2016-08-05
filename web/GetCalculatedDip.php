<?php
function _mycalc($cnfg,&$avgval,&$tdenom,&$dips)
{
	$vals = explode('|',$cnfg->startidx);
	if(count($vals) < 1) return true;
	$indx = intval($vals[0]);
	if(!isset($dips[$indx+1])) return true;
	$avgsize = intval($cnfg->avgsize);
	if($avgsize < 2) return true;
	$fval = 0.0;
	$denom = 0.0;
	$num = count($dips);
	for($i=($indx+1); $i<$num and $i<($indx+$avgsize+1); $i++)
	{
		//echo "<p>dp = $i {$dips[$i]}</p>";
		$fval += floatval($dips[$i]);
		$denom += 1.0;
	}
	$avgval += round(($fval / $denom),3);
	$tdenom += 1.0;
	return true;
}
	
function GetCalculatedDip(&$db,&$dip)
{
	$sql = 'select * from welllogs order by endmd desc limit 1';
	$db->DoQuery($sql);
	$lastendmd='';
	$lastbias='';
	$lastscale='';
	if($db->FetchRow())
	{
		$lastendmd = $db->FetchField("endmd");
		$lastbias = $db->FetchField("scalebias");
		$lastscale = $db->FetchField("scalefactor");
	}
	//echo "<p>lastendmd=$lastendmd lastbias=$lastbias lastscale=$lastscale</p>\n";
	
	// get the autodipconfig parameters
	
	$db->DoQuery('select autodipconfig from wellinfo');
	if($db->FetchRow()) {
		$autodipconfig = $db->FetchField('autodipconfig');
	}
	//echo "<p>autodipconfig = " . htmlspecialchars($autodipconfig) . "</p>\n";
	$auto_dip_cnfgs = json_decode($autodipconfig);
	//echo "<pre>"; print_r($auto_dip_cnfgs); echo "</pre>\n";
	
	// get the controllogs
	
	$db->DoQuery('select * from controllogs order by tablename');
	if ($db->FetchRow()) {
		$tablename=$db->FetchField("tablename");
		$startmd=$db->FetchField("startmd");
		$endmd=$db->FetchField("endmd");
		$cltot=$db->FetchField("tot");
		$clbot=$db->FetchField("bot");
		$cldip=$db->FetchField("dip");
		$stregdipazm=$db->FetchField("azm");
	}
	
	// create control options for Average Control Dip-Closure calculation

	$db->DoQuery('select * from wellplan where hide = 0 order by md desc');
	$num = $db->FetchNumRows();
	$control_options = array();
	for($i=0; $i<$num; $i++) {
		$db->FetchRow();
		if($i==0) continue;
		$md=sprintf("%.2f", $db->FetchField("md"));
		$nsraw = $db->FetchField("ns");
		$ewraw = $db->FetchField("ew");
		$closure = atan2($ewraw,$nsraw);
		$regazm = deg2rad($stregdipazm);
		$tregdip=sprintf("%.2f",atan(tan(deg2rad($cldip))*cos($regazm - $closure))*(180/pi()));
		$control_options[$i] = "<option value='$i|$tregdip'>$md($tregdip)</option>";
		$control_dips[$i] = $tregdip;
	//	array_push($control_options,"<option value='$i|$tregdip'>$md($tregdip)</option>");
	}
	
	// create survey options for Average Real Dip-Closure calculation and
	// avarage options for Average Dip calculation
	
	$db->DoQuery("select * from surveys where hide = 0 and plan = 0 order by md desc");
	$num=$db->FetchNumRows();
	$survey_options=array();
	$average_options=array();
	for($i=0; $i<$num; $i++) {
		$db->FetchRow();
		$md=sprintf("%.2f", $db->FetchField("md"));
		$vdip=sprintf("%.2f",$db->FetchField("dip"));
		$nsraw = $db->FetchField("ns");
		$ewraw = $db->FetchField("ew");
		$closure = atan2($ewraw,$nsraw);
		$regazm = deg2rad($stregdipazm);
		$tregdip=sprintf("%.2f",atan(tan(deg2rad($cldip))*cos($regazm - $closure))*(180/pi()));
		$survey_options[$i] = "<option value='$i|$tregdip'>$md($tregdip)</option>";
		$average_options[$i] = "<option value='$i|$vdip'>$md($vdip)</option>";
		$survey_dips[$i] = $tregdip;
		$average_dips[$i] = $vdip;
	//	array_push($survey_options,"<option value='$i|$tregdip'>$md($tregdip)</option>");
	//	array_push($average_options,"<option value='$i|$vdip'>$md($vdip)</option>");
	}
	
	// calculate the dip
	
	$tdenom = 0.0;
	$avgval = 0.0;
	foreach($auto_dip_cnfgs as $auto_dip_cnfg)
	{
		if($auto_dip_cnfg->type == 'man')
		{
			$avgval += floatval($auto_dip_cnfg->avgval);
			$tdenom += 1.0;
		}
		elseif($auto_dip_cnfg->type == 'ad')
		{
			_mycalc($auto_dip_cnfg,$avgval,$tdenom,$average_dips);
		}
		elseif($auto_dip_cnfg->type == 'acdc')
		{
			_mycalc($auto_dip_cnfg,$avgval,$tdenom,$control_dips);
		}
		elseif($auto_dip_cnfg->type == 'ardc')
		{
			_mycalc($auto_dip_cnfg,$avgval,$tdenom,$survey_dips);
		}
	}
	$avgval = $avgval / $tdenom;
	$dip = sprintf('%f',round($avgval,3));

	//echo "<p>control_options =<br>\n";
	//foreach($control_options as $co) echo htmlspecialchars($co) . "<br>\n";
	//echo "</p>\n";
	//echo "<p>survey_options =<br>\n";
	//foreach($survey_options as $so) echo htmlspecialchars($so) . "<br>\n";
	//echo "</p>\n";
	//echo "<p>average_options =<br>\n";
	//foreach($average_options as $ao) echo htmlspecialchars($ao) . "<br>\n";
	//echo "</p>\n";
	//echo "<p>average_dips =<br>\n";
	//foreach($average_dips as $ad) echo "$ad<br>";
	//echo "</p>\n";

	return true;
}
?>
