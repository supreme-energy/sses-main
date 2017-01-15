<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
$returnto=$_POST['returnto'];
$seldbname=$_POST['seldbname'];
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="gva_styles.css" />
</head>
<SCRIPT language="javascript">
function show_alert(rowform)
{
	var r=confirm("Ready to import CSV file?");
	if (r==true)
  	{
		t = 'surveysupload.php';
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
	<A class='menu' href='<?echo $returnto;?>?seldbname=<?echo $seldbname?>'>Return</A>
	<H1>Survey CSV File To Import:</H1>
	<INPUT type='hidden' name='returnto' value='<?echo $returnto;?>'>
	<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<INPUT type="file" name="userfile" size="70">
	<BR>
	<BR>
	<INPUT type="submit" value="Import" onclick="show_alert(this.form)">
	</FORM>
</td>
</tr>
<tr>
<td>
	<center><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></center>
</td>
</tr>
</table>
</body>
</html>
