<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

$seldbname=$_POST['seldbname'];
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="textout.css" />
<LINK rel='stylesheet' type='text/css' href='waitdlg.css'/>
<title><?echo $seldbname;?>-SGTA Front Page</title>
<script language="javascript">
var ray = {
	ajax:function(st) { this.show('load'); },
	show:function(el) { this.getID(el).style.display=''; },
	getID:function(el) { return document.getElementById(el); }
}
</script>
</head>
<body>
<?include("waitdlg.html");?>
<form action="dbuploader.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="seldbname" value="<?echo $seldbname;?>">
<input type="hidden" name="MAX_FILE_SIZE" value="999999999">
<table class='container'>
<tr>
<td colspan='2'>
	<A href="dbindex.php?seldbname=<?echo $seldbname;?>"> Back to database index </A>
	<br>
	<br>
	Database backup file to restore from:
</td>
</tr>
<tr>
<td>
	<input name="userfile" type="file" size="45"><br>
</td>
<td>
	<input type="submit" value="Send file" onclick="return ray.ajax()">
</td>
</tr>
</table>
</form>
<div id="load" style="display:none;">Submitting... Please wait</div>
</body>
</html>
