<?php
require_once("dbio.class.php");
require_once("classes/Survey.class.php");
$survey_loader = new Survey($_REQUEST);
$surveys=$survey_loader->get_surveys('rows');
$bitproj = $survey_loader->get_bitProjection($surveys);
$projs   = $survey_loader->get_projs('rows');
$seldbname=$_GET['seldbname'];
print_r($bitproj['inc']);
$ret=$_GET['ret'];
$db=new dbio($seldbname);
$db->OpenDb();
?>
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>Oujia</TITLE>
<LINK rel='stylesheet' type='text/css' href='projavgdip.css'/>
</HEAD>
<SCRIPT language="javascript">
	var caculate_slide=function(){
		pinc = document.getElementById('present_inc').value
		pazm = document.getElementById('present_azm').value
		tinc = document.getElementById('proj_inc').value
		tazm = document.getElementById('proj_azm').value
		my   = document.getElementById('motor_yield').value
		pi   = Math.PI
		v1   = Math.sin(pi/180*pinc)*Math.sin(pi/180*tinc)+Math.cos(pi/180*pinc)*Math.cos(pi/180*tinc)
		v3a  = Math.abs(pazm-tazm)
		v2   = Math.cos(pi/180*v3a)
		v3   =  tinc - pinc
		v4   = v2*v1
		v5   =Math.acos(v4)*180/pi
		v6   = v3/v5
		v7   = Math.acos(v6)*180/pi
		tf_n    = v7
		slide_n = v5/my*100
		document.getElementById('tfn').value=tf_n;
		document.getElementById('sliden').value=slide_n;
	}
</SCRIPT>
<BODY>
<CENTER>
<TABLE class='tabcontainer' style='width=200px'>
<TR><TD colspan='2'><H2>Oujia</H2></TD></TR>

<FORM id='oform' name='oform' method='post'>

<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
<INPUT type='hidden' name='username' value='<?echo $username;?>'>
<INPUT type='hidden' name='ret' value='<?echo $ret;?>'>
<TR><td></td><td>Present</td><td>Proj</td><td></td></tr>
<tr><td>INC</td><td><input id='present_inc' type='text' value='<?echo $bitproj['inc']?>'></td><td><input id='proj_inc' type='text' value='<?echo $projs[count($projs)-1]['inc']?>'></td><td></td></tr>
<tr><td>AZ</td><td><input id='present_azm' type='text' value='<?echo $bitproj['azm']?>'></td><td><input id='proj_azm' type='text' value='<?echo $projs[count($projs)-1]['azm']?>'></td><td></td></tr>
<tr><td>Motor Yield</td><td><input id='motor_yield' type='text'></td><td></td><td></td></tr>
<tr><td>TF Needed</td><td><input id='tfn' value=''></td><td></td><td></td></tr>
<tr><td>Slide</td><td><input id='sliden' value=''></td><td></td><td></td></tr>
<tr><td colspan='4'><button type="button" onclick="caculate_slide()">Calculate</button>
<td colspan='4'>
	<br>
	<small>
	<small>
	&#169; 2010-2011 Digital Oil Tools
	</small>
	</small>
</td>
</tr>
</TABLE>
</CENTER>
</BODY>
</HTML>
<? $db->CloseDb(); ?>
