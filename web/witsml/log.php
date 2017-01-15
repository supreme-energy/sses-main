<?php
include('include.php');
/*
 * Created on Jul 11, 2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $data_set = "<log>hi</log>";
 echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
?>
<logs xmlns="http://www.witsml.org/schemas/1series" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.witsml.org/schemas/1series  ../xsd_schemas/obj_log.xsd" version="1.4.1.0">
<documentInfo>
		<documentName>Log</documentName>
		<fileCreationInformation>
			<fileCreationDate><? echo $now?></fileCreationDate> 
			<fileCreator><? echo $system_name?></fileCreator>
		</fileCreationInformation>
	</documentInfo>
	<? echo $data_set?>
</logs>
