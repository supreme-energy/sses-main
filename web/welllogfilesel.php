<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

$seldbname=$_POST['seldbname'];
$ret=$_POST['ret'];
$scrolltop=$_POST['scrolltop'];
$scrollleft=$_POST['scrollleft'];
$zoom=$_POST['zoom'];
?>
<!doctype html>
<head>
<link rel="stylesheet" type="text/css" href="gva_styles.css" />
</head>
<body>

<form method="post" enctype="multipart/form-data">
<input type='hidden' name='ret' value='<?echo $ret;?>'>
<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
<input type='hidden' name='zoom' value='<?echo $zoom?>'>

<TABLE class='container'>
<tr>
<td>
	<A class='menu' href='<?echo "$ret?seldbname=$seldbname";?>'>Return</A>
	<h1>LAS File To Import:</h1>
</td>
</tr>
<tr>
<td class="container" align='left'>
	<input type="file" name="userfile" size="90">
</td>
</tr>
<tr>
<td class="container" align='right'>
	<input type="submit" value="Import" onclick="show_alert(this.form)">
</td>
</tr>
<tr>
<td>
	<center><small><small>&#169; 2010-2011 Digital Oil Tools</small></small></center>
</td>
</tr>
</table>

</form>

<script language="javascript">
function show_alert(rowform)
{
	// var r=confirm("Ready to import LAS file?");
	// if (r==true)
  	// {
		t = 'welllogupload.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return true;
  	// }
	// rowform.userfile.value="";
	// document.location=rowform.ret.value;
	// return ray.ajax();
}
var ray={
ajax:function(st) { this.show('load'); },
show:function(el) { this.getID(el).style.display=''; },
getID:function(el) { return document.getElementById(el); }
}
</script>

</body>
</html>
