<?php
$surveys = array();

function CalcValuesInInterval($slide,&$db,$from_md,$to_md,&$md,&$bur,&$turn_rate,&$motor_yield)
{
	global $surveys;

	// collect survey data once for this function call

	if(count($surveys) == 0)
	{
		$sql = 'select md, radians(inc) inc, radians(azm) azm, cl, dl from surveys where plan = 0 order by md';
		$db->DoQuery($sql);
		if($db->FetchNumRows() < 1) return 'No';
		while(($row = $db->FetchRow()))
		{
			$surveys[] = array('md' => floatval($row['md']),
				'inc' => floatval($row['inc']),
				'azm' => floatval($row['azm']),
				'cl' => floatval($row['cl']),
				'dl' => floatval($row['dl']));
		}
	}

	$md = '';
	$bur = '';
	$turn_rate = '';
	$motor_yield = '';

	$slide_len = 0.0;
	$slide_cl = 0.0;
	$slide_dl = 0.0;

	$found_survey = false;

	// look for a survey that falls in this interval

	foreach($surveys as $key => $survey)
	{
		if($key == 0) continue;

		if($survey['md'] > $from_md and $survey['md'] <= $to_md)
		{
			$md = sprintf('%.2f',round($survey['md'],2));
			$cl = $survey['cl'];
			$inc = $survey['inc'];
			$pinc = $surveys[$key-1]['inc'];
			$azm = $survey['azm'];
			$pazm = $surveys[$key-1]['azm'];
//			$dl = acos(cos($inc - $pinc) - (sin($inc) * sin($pinc) * (1 - cos($azm - $pazm))));
//			$dl = rad2deg($dl);
			$dls = $survey['dl'];
			if($cl > 0.0)
			{
				$bur = sprintf('%.2f',round(100.0 * ($pinc - $inc) / $cl,2));
				$turn_rate = sprintf('%.2f',round(100.0 * ($pazm - $azm) / $cl,2));
			}
			//echo "<p>found survey $md at interval ($from_md,$to_md), bur=$bur, turn_rate=$turn_rate</p>";

			if($slide)
			{
				$slide_len = $survey['md'] - $from_md;
				$slide_cl = $survey['cl'];
				$slide_dl = $survey['dl'];
			}

			$found_survey = true;

			break;
		}
	}

	// if this is a slide and not survey was found then measure the whole slide and get the
	// curve lenght of the following survey

	if($slide and $slide_len < 0.01)
	{
		$slide_len = $to_md - $from_md;

		foreach($surveys as $key => $survey)
		{
			if($key == 0) continue;

			if($survey['md'] >= $to_md)
			{
				$md = sprintf('%.2f',round($survey['md'],2));
				$cl = $survey['cl'];
				$inc = $survey['inc'];
				$pinc = $surveys[$key-1]['inc'];
				$azm = $survey['azm'];
				$pazm = $surveys[$key-1]['azm'];
				$dls = $survey['dl'];
				if($cl > 0.0)
				{
					$bur = sprintf('%.2f',round(100.0 * ($pinc - $inc) / $cl,2));
					$turn_rate = sprintf('%.2f',round(100.0 * ($pazm - $azm) / $cl,2));
				}
				$slide_cl = $survey['cl'];
				$slide_dl = $survey['dl'];

				$found_survey = true;

				break;
			}
		}
	}

	// calculate the motor yield

	if($slide and $slide_len > 0.0 and $found_survey == true)
		$motor_yield = sprintf('%.2f',round($slide_dl * $slide_cl / $slide_len,2));

	return true;
}

function GetRotSlideVsFromMd(&$db,$smdval)
{
	$sql = "select * from welllogs where startmd < $smdval and endmd >= $smdval";
	$db->DoQuery($sql);
	if($db->FetchNumRows() != 1) return 'No';
	$row = $db->FetchRow();
	$mdval = floatval($smdval);
	$startmd = floatval($row['startmd']);
	$endmd = floatval($row['endmd']);
	$startvs = floatval($row['startvs']);
	$endvs = floatval($row['endvs']);
	if(($endmd - $startmd) == 0.0) return '0';
	$mdrat = ($mdval - $startmd) / ($endmd - $startmd);
	$newvs = $startvs + ($mdrat * ($endvs - $startvs));
	return sprintf('%.0f',round($newvs,0));
}

function ImportRotSlideFromFile(&$db,$filename)
{
	// check that the import file exists

	if(!file_exists($filename))
	{
		echo "<p>Rotate Slide import file ($filename) doesn't exist!</p>\n";
		return false;
	}

	// check that the table exists

	if($db->TableExists('rotslide') === false)
	{
		echo "<p>Database table 'rotslide' doesn't exist</p>\n";
		return false;
	}

	// clear the table

	$db->DoQuery('truncate rotslide');

	// get the data from the file

	$data = file($filename);
	foreach($data as $key => $line)
	{
		if(trim($line) == '') unset($data[$key]);
		elseif(substr(trim($line),0,4) == ',,,,') unset($data[$key]);
	}
	//echo '<pre>'; print_r($data); echo '</pre>';

	$lastmd = '0';
	$lastvs = '0';
	$num = 0;
	foreach($data as $line)
	{
		$vals = explode(',',$line,20);
		if(count($vals) < 5) continue;
		//echo "<pre>$line\n"; print_r($vals); echo '</pre>';
		$rotstartmd = '0';
		$rotendmd = '0';
		$slidestartmd = '0';
		$slideendmd = '0';
		$tfo = '';
		$rotstartvs = '0';
		$rotendvs = '0';
		$slidestartvs = '0';
		$slideendvs = '0';

		// the following section gets the VS equivalents for any of the the MD values provided

		if(is_numeric($vals[0]))
		{
			$rotstartmd = trim($vals[0]);
			if($rotstartmd == $lastmd) $rotstartvs = $lastvs;
			else
			{
				if(($rotstartvs = GetRotSlideVsFromMd($db,$rotstartmd)) === 'No') continue;
				$lastmd = $rotstartmd;
				$lastvs = $rotstartvs;
			}
		}
		if(is_numeric($vals[1]))
		{
			$rotendmd = trim($vals[1]);
			if($rotendmd == $lastmd) $rotendvs = $lastvs;
			else
			{
				if(($rotendvs = GetRotSlideVsFromMd($db,$rotendmd)) === 'No') continue;
				$lastmd = $rotendmd;
				$lastvs = $rotendvs;
			}
		}
		if(is_numeric($vals[2]))
		{
			$slidestartmd = trim($vals[2]);
			if($slidestartmd == $lastmd) $slidestartvs = $lastvs;
			else
			{
				if(($slidestartvs = GetRotSlideVsFromMd($db,$slidestartmd)) === 'No') continue;
				$lastmd = $slidestartmd;
				$lastvs = $slidestartvs;
			}
		}
		if(is_numeric($vals[3]))
		{
			$slideendmd = trim($vals[3]);
			if($slideendmd == $lastmd) $slideendvs = $lastvs;
			else
			{
				if(($slideendvs = GetRotSlideVsFromMd($db,$slideendmd)) === 'No') continue;
				$lastmd = $slideendmd;
				$lastvs = $slideendvs;
			}
		}

		// the following section will get the CL for either the Rotate or Slide

		$md = '';
		$bur = '';
		$turn_rate = '';
		$motor_yield = '';

		if($rotstartmd > 0 and $rotendmd > 0)
		{
			CalcValuesInInterval(false,$db,floatval($rotstartmd),floatval($rotendmd),$md,$bur,$turn_rate,$motor_yield);
			//echo "<p>rotstartmd=$rotstartmd rotendmd=$rotendmd bur=$bur turn_rate=$turn_rate</p>";
		}
		elseif($slidestartmd > 0 and $slideendmd > 0)
		{
			CalcValuesInInterval(true,$db,floatval($slidestartmd),floatval($slideendmd),$md,$bur,$turn_rate,$motor_yield);
			//echo "<p>slidestartmd=$slidestartmd slideendmd=$slideendmd bur=$bur turn_rate=$turn_rate</p>";
		}

		//echo "<p>rotmd=$rotstartmd,$rotendmd rotvs=$rotstartvs,$rotendvs<br>\n";
		//echo "slidemd=$slidestartmd,$slideendmd slidevs=$slidestartvs,$slideendvs</p>\n";
		$sql = "insert into rotslide (rotstartmd,rotendmd,slidestartmd,slideendmd," .
			"rotstartvs,rotendvs,slidestartvs,slideendvs,tfo,md,bur,turn_rate,motor_yield) " .
			"values ($rotstartmd,$rotendmd,$slidestartmd,$slideendmd," .
			"$rotstartvs,$rotendvs,$slidestartvs,$slideendvs,'{$vals[4]}','$md','$bur','$turn_rate','$motor_yield')";
		//echo "<p>sql=$sql</p>";
		$db->DoQuery($sql);
		$num++;
	}

	return $num;
}

function RecalcValuesInIntervals(&$db)
{
	$db->DoQuery('select * from rotslide');
	if(($num = $db->FetchNumRows()) < 1) return true;
	//echo "<p>found $num records</p>";
	$data = array();
	while(($row = $db->FetchObj())) $data[] = $row;
	foreach($data as $row)
	{
		//echo '<pre>'; print_r($row); echo '</pre>';

		$rotstartmd = floatval($row->rotstartmd);
		$rotendmd = floatval($row->rotendmd);
		$slidestartmd = floatval($row->slidestartmd);
		$slideendmd = floatval($row->slideendmd);
		$rotstartvs = '0';
		$rotendvs = '0';
		$slidestartvs = '0';
		$slideendvs = '0';

		if($rotstartmd != '0')
		{
			if(($rotstartvs = GetRotSlideVsFromMd($db,$rotstartmd)) === 'No') continue;
		}
		if($rotendmd != '0')
		{
			if(($rotendvs = GetRotSlideVsFromMd($db,$rotendmd)) === 'No') continue;
		}
		if($slidestartmd != '0')
		{
			if(($slidestartvs = GetRotSlideVsFromMd($db,$slidestartmd)) === 'No') continue;
		}
		if($slidestartmd != '0')
		{
			if(($slideendvs = GetRotSlideVsFromMd($db,$slideendmd)) === 'No') continue;
		}

		$md = '';
		$bur = '';
		$turn_rate = '';
		$motor_yield = '';

		if($rotstartmd > 0 and $rotendmd > 0)
		{
			CalcValuesInInterval(false,$db,$rotstartmd,$rotendmd,$md,$bur,$turn_rate,$motor_yield);
			//echo "<p>rotstartmd=$rotstartmd rotendmd=$rotendmd bur=$bur turn_rate=$turn_rate</p>";
		}
		elseif($slidestartmd > 0 and $slideendmd > 0)
		{
			CalcValuesInInterval(true,$db,$slidestartmd,$slideendmd,$md,$bur,$turn_rate,$motor_yield);
			//echo "<p>slidestartmd=$slidestartmd slideendmd=$slideendmd bur=$bur turn_rate=$turn_rate</p>";
		}
		$sql = "update rotslide set rotstartvs = $rotstartvs, rotendvs = $rotendvs, slidestartvs = $slidestartvs, " .
			"slideendvs = $slideendvs, md = '$md', bur = '$bur', turn_rate = '$turn_rate', motor_yield = '$motor_yield' " .
			"where rsid = {$row->rsid}";
		$db->DoQuery($sql);
	}
	return true;
}
?>
