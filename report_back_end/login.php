<?php
	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		$retar = login($_REQUEST);

	if(!$retar['status']){?>
		<script>
			$( "#error_popup" ).show()
			$("#error_message_area").html('<?php echo $retar['message']?>');
		</script>
	<?} else {
		header("Location: index.php?cmd=report_list");	
	}
	}?>
<form action='index.php?cmd=login' method='post'>
<table id='main' class='tabcontainer' width='300px' height='175px'>
<tr><td align='center'>Username <input type='text' style='text-align:left' name='username' value="<?echo $_REQUEST['username']?>"></td></tr>
<tr><td align='center'>Password <input type='password' style='text-align:left' name='password'  value="<?echo $_REQUEST['password']?>"></td></tr>
<tr><td align='center'><input type='submit' value='login'></td></tr>
<tr>
<td colspan='16'>
	<br><center><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></center>
</td>
</tr>
</table>
</form>