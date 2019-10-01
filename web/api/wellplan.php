 <?php
 include("api_header.php");
 include "../readwellinfo.inc.php";
 include "../readappinfo.inc.php";
 include("../gvatab2_include.php");
 //exec ("../sses_cc -d $seldbname -w");
 //$logsw="";// if($uselogscale>0)	$logsw="-log";
 //exec("../sses_pd -T $tablename -d $seldbname -o $fn -w 340 -h 750 -s $startmd -e $endmd -r $scaleright $logsw");
 $db->DoQuery("SELECT * FROM wellplan WHERE hide=0 ORDER BY md ASC");
 $num=$db->FetchNumRows();
 $i=0;
 $result = array();
 while ($i < $num) {
 	$db->FetchRow();
 	$id=$db->FetchField("id");
 	$plan=sprintf("%d", $db->FetchField("plan"));
 	$md=sprintf("%.2f", $db->FetchField("md"));
 	$inc=sprintf("%.2f", $db->FetchField("inc"));
 	$azmraw = $db->FetchField("azm");
 	$caraw = $db->FetchField("ca");
 	$cdraw = $db->FetchField("cd");
 	$azm=sprintf("%.2f", $db->FetchField("azm"));
 	$tvd=sprintf("%.2f", $db->FetchField("tvd"));
 	$vs=sprintf("%.2f", $db->FetchField("vs"));
 	$nsraw = $db->FetchField("ns");
 	$ns=sprintf("%.2f", $db->FetchField("ns"));
 	$ewraw = $db->FetchField("ew");
 	$ew=sprintf("%.2f", $db->FetchField("ew"));
 	$cd=sprintf("%.2f", $db->FetchField("cd"));
 	$ca=sprintf("%.2f", $db->FetchField("ca"));
 	$dl=sprintf("%.2f", $db->FetchField("dl"));
 	$closure = atan2($ewraw,$nsraw);
 	$regazm = deg2rad($stregdipazm);
 	if($i!=0){
 		$tregdip=sprintf("%.2f",atan(tan(deg2rad($cldip))*cos($regazm - $closure))*(180/pi()));
 	}else{
 		$tregdip=0;
 	}
 	++$i;
 	array_push($result, array(
 	        'id'  => $id,
 			'num' => $i,
 			'md' => $md,
 			'inc' => $inc,
 			'azm' => $azm,
 			'tvd' => $tvd,
 			'vs'  => $vs,
 			'ns'  => $ns,
 			'ew'  => $ew,
 			'cd'  => $cd,
 			'ca'  => $ca,
 			'dl'  => $dl,
 			'dip-c' => $tregdip
 			
 	));
 	}
 	$db->CloseDb();
 	echo json_encode($result);
 ?>