<?
require_once("dbio.class.php");
$seldbname=$_REQUEST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
$query = "select * from surveys where plan = 1";
$db->DoQuery($query);
$db->FetchRow();
$bprjvs = round($db->FetchField("vs"),2);
$bprjtvd = round($db->FetchField("tvd"),2);
$bprjtcl = round($db->FetchField("tot"),2);
$query = "select vslon,vsldip,vsland from wellinfo";
$db->DoQuery($query);
$db->FetchRow();
$vsland = $db->FetchField("vsland");
$vsldip = $db->FetchField("vsldip");
$vslon  = $db->FetchField("vslon");
$status = $vslon==1?'enabled':'disabled';
$enable_button = $vslon==1?'display:none':'display:inline';
$disable_button = $vslon==0?'display:none':'display:inline';
?>
<HTML>
<HEAD>
<TITLE>Fixed Landing</TITLE>
<LINK rel='stylesheet' type='text/css' href='projavgdip.css'/>
<SCRIPT language="javascript">
	var disable_landing=function(){
		document.getElementById("landing_enable").style.display='inline';
		document.getElementById("landing_disable").style.display='none';
		document.getElementById("landing_status").innerHTML='disabled';
		xmlhttp=new XMLHttpRequest();
		xmlhttp.open('GET','/sses/fixedlandingsave.php?seldbname=<?=$seldbname?>&field=vslon&value=0',true);
		xmlhttp.send();
	}
	var enable_landing=function(){
		document.getElementById("landing_enable").style.display='none';
		document.getElementById("landing_disable").style.display='inline';
		document.getElementById("landing_status").innerHTML='enabled';
		xmlhttp=new XMLHttpRequest();
		xmlhttp.open('GET','/sses/fixedlandingsave.php?seldbname=<?=$seldbname?>&field=vslon&value=1',true);
		xmlhttp.send();
	}
	var change_vsland=function(){
		calculate_fixed();
		val = document.getElementById('vsland').value
		xmlhttp=new XMLHttpRequest();
		xmlhttp.open('GET','/sses/fixedlandingsave.php?seldbname=<?=$seldbname?>&field=vsland&value='+val,true);
		xmlhttp.send();
	}
	var change_vsldip=function(){
		calculate_fixed();
		val = document.getElementById('vsldip').value
		xmlhttp=new XMLHttpRequest();
		xmlhttp.open('GET','/sses/fixedlandingsave.php?seldbname=<?=$seldbname?>&field=vsldip&value='+val,true);
		xmlhttp.send();
	}
	var calculate_fixed=function(){
		bprjvs = parseFloat(document.getElementById('bprjvs').value)
		vsland = parseFloat(document.getElementById('vsland').value)
		bittvd = parseFloat(document.getElementById('bit_tvd').value)
		vsldip = parseFloat(document.getElementById('vsldip').value)
		bitpostcl = parseFloat(document.getElementById('bitpostcl').value)
		ltvdvsl=(bprjvs - vsland)*Math.tan((vsldip*(Math.PI/180)))+bittvd+(bitpostcl);
		document.getElementById('ltvdvsl').value=ltvdvsl.toFixed(2);
	}
</SCRIPT>
</HEAD>
<BODY>
<CENTER>
<TABLE class='tabcontainer' style='width=200px'>
<TR><TD colspan='2' align='center'><H2>Fixed Landing</H2></TD></TR>
<tr><td clospan='4'>Fix landing is currently <span style='text-weight:bold' id='landing_status'><? echo $status?></span>.
<button onclick="enable_landing()" id='landing_enable' style="<? echo $enable_button?>"> Enable Fixed Landing </button>
<button onclick="disable_landing()" id='landing_disable' style="<? echo $disable_button?>"> Disable Fixed Landing </button></td></tr>
<form id='oform' name='oform' method='post'>
<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
<INPUT type='hidden' name='username' value='<?echo $username;?>'>
<INPUT type='hidden' name='ret' value='<?echo $ret;?>'>
<TR><td colspan='2'>Calculated data from TT</td><td colspan='2'>Input Data</td></tr>
<tr><td>Bprj VS</td><td><input id='bprjvs' type='text' value='<? echo $bprjvs?>' disabled></td>
<td>VSLand</td><td><input id='vsland' type='text' value='<? echo $vsland?>' onchange="change_vsland()"></td></tr>
<tr><td>Bit TVD</td><td><input id='bit_tvd' type='text' value='<? echo $bprjtvd?>' disabled></td>
<td>Dip</td><td><input id='vsldip' type='text' value='<? echo $vsldip?>' onchange="change_vsldip()"></td></tr>
<tr><td>Bit Pos-TCL</td><td><input id='bitpostcl' type='text' value ='<? echo $bprjtcl-$bprjtvd?>' disabled></td><td></td><td></td></tr>
<tr colspan='4'><td>Result</td></tr>
<tr><td>Landing TVD at Fixed VSland</td><td><input id='ltvdvsl' value='' disabled></td><td></td><td></td></tr>
<tr><td colspan='2'>
	<button onclick="window.close()">Close</button>
</td></tr>
</form>
<tr><td colspan='4'><br><small><small>&#169; 2010-2011 Digital Oil Tools</small></small></td></tr>
</TABLE>
</CENTER>
<script>
	calculate_fixed();
</script>
</BODY>
</HTML>
<? $db->CloseDb(); ?>
