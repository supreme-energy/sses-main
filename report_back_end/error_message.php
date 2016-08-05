<?php
/*
 * Created on Jul 3, 2013
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $message = $_SESSION['error_message'];
 $_SESSION['error_message']=null;
?>
<div id='error_popup' class="error">
<div style='text-align:right;font-size:12pt;color:black;'><a style='cursor:pointer' onclick="$('#error_popup').hide()">x</a></div>
<div id='error_message_area'><?echo $message?></div>
</div>