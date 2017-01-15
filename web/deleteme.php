<?php
	$ip =   $_REQUEST['ip'];
	$name = $_REQUEST['name'];
	$date =date('m-d-y-h-i-s');
	echo $ip;
	$nip=str_replace('.','-',$ip);
	$fname = $date.$nip.'_'.$name;
	shell_exec("pg_dumpall --user=umsdata > /tmp/$fname.dmpall");
	shell_exec("zip -j /tmp/$fname.dmpall.zip  /tmp/$fname.dmpall");
	$connid = ftp_connect('166.78.61.16');
	ftp_login($connid, 'ftpuser','d0N0tst33l');
	if (ftp_put($connid, "/tmp/$fname.dmpall.zip", "/tmp/$fname.dmpall.zip", FTP_ASCII)) { 
    	echo "successfully uploaded $file\n"; 
 	} else { 
    	echo "There was a problem while uploading $file\n"; 
    } 
    shell_exec("rm /tmp/$fname.dmpall");
    shell_exec("rm /tmp/$fname.dmpall.zip");
 // close the connection 
 	ftp_close($connid); 
?>