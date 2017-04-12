<?php

?>
No go zones are defined using the min vs, min tvd and the max vs, max tvd
<form method='post' action='nogo_zone_create.php' name='nogo_zone_form' onsubmit='return false;'>
	<input type='hidden' name='seldbname' value='<?php echo $_REQUEST['seldbname']?>'>
	<table>
		<tr>
		<td colspan='2'>
			<table>
				<tr><td>Start VS</td>
					<td ><input name='anno_date' type='text' id='date'></td>
					<td>End VS</td>
					<td><input name ='anno_time' type='text' id='time'></td>
					<td><button id='settcurrent'>Set To Current Date and Time</button></td>
				</tr>
				<tr><td>Start TVD</td>
					<td ><input name='anno_date' type='text' id='date'></td>
					<td>End TVD</td>
					<td><input name ='anno_time' type='text' id='time'></td>
					<td><button id='settcurrent'>Set To Current Date and Time</button></td>
				</tr>
			</table>
		</td>
		<td>Choose Survey</td><td>
		<select name='annotation_survey'>
		<?foreach($surveys as $survey){
			if($survey['plan']==1)continue;
			?>
		<option value='<?php echo $survey['id']?>'>MD:<?php echo sprintf("%01.2f",$survey['md'])?>,INC:<?php echo sprintf("%01.2f",$survey['inc'])?>,VS:<?php echo sprintf("%01.2f",$survey['vs'])?></option>
		<?}?>
		</select></td>
		</tr>
		<tr><td colspan='4'>Annotation Comment<input type='text' value='' maxlength="45" size='45' name='comment'></td></tr>
		<tr><td colspan='4'><input type='submit' value='Add Annotation' onclick='document.annotation_form.submit()'></td></tr>
	</table>
	</form>