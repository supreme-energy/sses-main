<?php
/*
 * Created on Aug 28, 2015
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 exec ("./sses_gpd_rv -d sgta_353 -w 320 -nrm 0 -h 3840 -s 6796.00 -e 10638 -o tmp/gammadepth.png -wld");
 exec ("./sses_gpd_rv -d sgta_353 -w 280 -h 3840 -nrm 1 -plamd 1 -s 6796.00 -e 10638 -o tmp/gammatvd.png -wld");
 exec ("./sses_gpd_rv -d sgta_353 -w 217 -h 3840 -nrm 1 -plamd 2 -s 6796.00 -e 10638 -o tmp/ropmd.png -nogrid");
?>
<div><img  src="imgs/header_data_sses.PNG"></div>
<div style="float:left"><img src="tmp/gammadepth.png"></div>
<div style="float:left"><img src="tmp/gammatvd.png"></div>
<div style="float:left"><img src="tmp/ropmd.png"></div>
