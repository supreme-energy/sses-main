<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
$seldbname=$_GET['seldbname'];
$color=$_GET['color'];
$infoid=$_GET['infoid'];
?>
<html>
<HEAD>
<LINK rel='stylesheet' type='text/css' href='addformscolor.css'/>
<link rel="stylesheet" href="farbtastic.css" type="text/css" />
<TITLE><?echo $title?></TITLE>
</HEAD>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="farbtastic.js"></script>
<script type="text/javascript" charset="utf-8">
  $(document).ready(function() {
    $('#demo').hide();
    $('#picker').farbtastic('#color');
  });
</script>
<SCRIPT language="javascript">
function OnSubmit(rowform) {
	t = 'addformscolord.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return true;
}
</SCRIPT>
<BODY>
<div id="demo" style="color: red; font-size: 1.4em">
jQuery.js is not present. You must install jQuery in this folder for the color wheel to work.</div>
<FORM name='submit' id='submit' method='post'>
<table>
<INPUT type='hidden' name='seldbname' id='seldbname' value='<?echo $seldbname;?>'>
<INPUT type='hidden' name='infoid' id='infoid' value='<?echo $infoid;?>'>
<tr>
<td>
	<div id="picker"></div>
	<input type='submit' value='Save' onclick='OnSubmit(this.form);'>
</td>
<td>
	<div class="form-item">
	<input style='color:white;' type="text" readonly='true' size='3' id="color" name="color" value="<?echo "#$color"?>" />
	</div>
</td>
</tr>
</table>
</FORM>
</BODY>
</html>
