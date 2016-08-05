<?php
/*
 * Created on Aug 3, 2014
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	#www-data ALL=NOPASSWD: /var/www/html/sses
 	exec('sudo /etc/init.d/postgresql restart');
 	exec('sudo /etc/init.d/apache2 graceful');
 	echo 'sequence executed';
?>
