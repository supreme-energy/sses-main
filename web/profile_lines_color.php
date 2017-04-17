<?php
$seldbname=$_GET['seldbname'];
$dbname = $_GET['dbname'];
$colorwp=$_GET['colorwp'];
?>
<html>
<HEAD>
<LINK rel='stylesheet' type='text/css' href='wellplancolor.css'/>
<link rel="stylesheet" href="farbtastic.css" type="text/css" />
<TITLE><?echo $title?></TITLE>
</HEAD>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="farbtastic.js"></script>
<script type="text/javascript" charset="utf-8">
  $(document).ready(function() {
    $('#demo').hide();
    $('#picker').farbtastic('#colorwp');
  });
</script>
<SCRIPT language="javascript">
function OnSubmit(rowform) {
	var opener = window.opener;
	if(opener){
		opener.document.forms['color_update_form_sgta_<?=$dbname?>'].color.value = rowform.colorwp.value
		opener.document.forms['color_update_form_sgta_<?=dbname?>'].submit()
	}else{

	}
	window.close()
	return false;
}
</SCRIPT>
<BODY>
<div id="demo" style="color: red; font-size: 1.4em">
jQuery.js is not present. You must install jQuery in this folder for the color wheel to work.</div>
<FORM name='submit' id='submit' method='post'>
<table>
<INPUT type='hidden' name='seldbname' id='seldbname' value='<?echo $seldbname;?>'>
<INPUT type='hidden' name='colorwp'  id='colorwp ' value='<?echo $colorwp;?>'>
<tr>
<td>
	<div id="picker"></div>
	<input type='submit' value='Save' onclick='OnSubmit(this.form);'>
</td>
<td>
	<div class="form-item">
	<input style='color:white;' type="text" readonly='true' size='3' id="colorwp" name="colorwp" value="<?echo "#$colorwp"?>" />
	</div>
</td>
</tr>
</table>
</FORM>
</BODY>
</html>