<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
$returnto=$_POST['returnto'];
$seldbname=$_POST['seldbname'];
?>
<head>
<link rel="stylesheet" type="text/css" href="gva_styles.css" />
</head>
<SCRIPT language="javascript">
function show_alert(rowform)
{
	var r=confirm("Ready to import LAS file?");
	if (r==true)
  	{
		t = 'controllogupload.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return true;
  	}
	rowform.userfile.value="";
	document.location=rowform.returnto.value;
	return ray.ajax();
}
var ray={
ajax:function(st) { this.show('load'); },
show:function(el) { this.getID(el).style.display=''; },
getID:function(el) { return document.getElementById(el); }
}
</SCRIPT>
<body>
<TABLE class='container'>
<td class="container" align='left'>
	<FORM method="post" enctype="multipart/form-data">
	<center>
	<big><big>Import Control Log LAS File</big></big>
	<br>
	<A class='menu' href='<?echo $returnto;?>'>Back To Wellplan</A>
	</center>
	<b>File to import from:</b>
	<br>
	<INPUT type='hidden' name='returnto' value='<?echo $returnto;?>'>
	<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<INPUT type="file" name="userfile" size="70">
</td>
</tr>
<tr>
<td align='right'>
	<INPUT type="submit" value="Import" onclick="show_alert(this.form)">
	</FORM>
</td>
</tr>
<tr>
<td>
	<center><small><small>&#169; 2010-2011 Digital Oil Tools</small></small></center>
</td>
</tr>
</table>
</body>
