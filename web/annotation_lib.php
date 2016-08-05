<?php
function AnnotationsCalcInZone(&$db,&$annos)
{
	// get the addforms data first (used for calculating TOT and BOT)

	$db->DoQuery('select * from addforms');
	$addforms = array();
	while(($row = $db->FetchRow())) $addforms[$row['label']] = $row;
	//echo "<pre style='font-size:12px'>addforms = "; print_r($addforms); echo '</pre>';

	// get all the survey data and calculate the Top Of Target (TOT) and Bottom Of Target (BOT) for each MD

	$ascdesc = 'asc';

	$sql = "select *, tot as tcl, tot - tvd as pos_tcl, id as svy, '' as tf from surveys order by md $ascdesc";
	$surveys = array();
	$db->DoQuery($sql);
	while(($row = $db->FetchRow())) { $surveys[] = $row; };
	//echo "<pre style='font-size:12px'>surveys = "; print_r($surveys); echo '</pre>';

	$db->error_print = true;

	$numsrv = count($surveys);
	foreach($surveys as $key =>$survey)
	{
		$tot = 'NF';
		$bot = 'NF';
		if(isset($addforms['TOT']))
		{
			if(trim($survey['plan']) == '1')
				$sql = "select tot from addformsdata where md={$survey['md']} and infoid={$addforms['TOT']['id']}";
			else
				$sql = "select tot from addformsdata where svyid={$survey['id']} and infoid={$addforms['TOT']['id']}";
			$db->DoQuery($sql);
			if(($row = $db->FetchRow()) !== false) $tot = $row['tot'];
		}
		if(isset($addforms['BOT']))
		{
			if(trim($survey['plan']) == '1')
				$sql = "select tot from addformsdata where md={$survey['md']} and infoid={$addforms['BOT']['id']}";
			else
				$sql = "select tot from addformsdata where svyid={$survey['id']} and infoid={$addforms['BOT']['id']}";
			$db->DoQuery($sql);
			if(($row = $db->FetchRow()) !== false) $bot = $row['tot'];
		}
		if(trim($survey['plan']) == '1') $surveys[$key]['svy'] = 'BPrj';
		else
		{
			if($ascdesc == 'asc') $surveys[$key]['svy'] = "$key";
			else $surveys[$key]['svy'] = strval($numsrv - $key - 1);
		}

		$surveys[$key]['tot'] = $tot;
		$surveys[$key]['bot'] = $bot;
	}
	//echo "<pre style='font-size:12px'>surveys = "; print_r($surveys); echo '</pre>';

	// go through the annotations and initial Meassured Depth (MD) of the surveys included

	$num = count($annos);
	$prev_md = 0.0;
	foreach($annos as $key => $anno)
	{
		if($anno['md'] == '' or intval($anno['md']) == 0) continue;
		if($key == 0) $annos[$key]['from_md'] = $surveys[0]['md'];
		else $annos[$key]['from_md'] = $prev_md;
		$prev_md = $anno['md'];
	}

	// go through the annotations and calculate the percent of times that the surveys included where
	//   in-target, in other words, between TOT and BOT

	$first_break = false; // flag of first time TVD is greater the TOT
	$first_vs = 0;        // VS of first survey point used
	foreach($annos as $key => $anno)
	{
		if($anno['md'] == '' or intval($anno['md']) == 0)
		{
			$annos[$key]['inzn'] = '';
			continue;
		}
		$total_num = 0;
		$total_in = 0;
		foreach($surveys as $survey)
		{
			if(floatval($survey['md']) < floatval($anno['from_md'])) continue;
			if(floatval($survey['md']) > floatval($anno['md'])) continue;
			$ftvd = floatval($survey['tvd']);
			$ftot = floatval($survey['tot']);
			$fbot = floatval($survey['bot']);

			// if TVD hasn't gone more than TOT check if this time it has

			if($first_break == false)
			{
				if($ftvd < $ftot) continue;
				$first_break = true;
				$first_vs = intval(round(floatval($survey['vs']),0));
			}

			//echo "<p>ftvd=$ftvd ftot=$ftot fbot=$fbot</p>";
			if($ftvd > $ftot and $ftvd < $fbot) $total_in += 1;
			$total_num++;
		}
		$annos[$key]['tot'] = "$total_num";
		$annos[$key]['totin'] = "$total_in";
		if($total_num < 1) $annos[$key]['inzn']= '';
		else $annos[$key]['inzn'] = sprintf('%.0f%%',round((floatval($total_in)/floatval($total_num)) * 100.0,0));
	}
	//echo "<pre style='font-size:12px'>"; print_r($annos); echo '</pre>';
	//echo "<p>first_vs=$first_vs</p>";

	return true;
}
?>
