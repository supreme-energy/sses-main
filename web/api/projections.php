 <?php
 include("api_header.php");
 $db2=new dbio($seldbname);
 $db2->OpenDb();
 $db->DoQuery("select * from addforms");
 $totid =null;
 $botid = null;
 $bot_thickness = 0;
 $tot_thickness = 0;
 while($db->FetchRow()){
 	if(trim($db->FetchField('label'))=='TOT'){
 		$tot_thickness = $db->FetchField("thickness");
 	    $totid = $db->FetchField('id');
 	}
 	if(trim($db->FetchField('label'))=='BOT'){
 	    $bot_thickness = $db->FetchField("thickness");
 	    $botid = $db->FetchField('id');
 	}
 }
 $projections_joined = array();
 $db->DoQuery("SELECT * FROM projections ORDER BY md $surveysort;");
 while($db->FetchRow()){
 	$svyid = $db->FetchField("id");
 	$bot = $db->FetchField("tot")+$bot_thickness;
 	$tot = $db->FetchField("tot")+$tot_thickness;
 	$projections_joined[]= array(
 			'id' => $db->FetchField("id") ,
 			'md' => sprintf("%.2f",$db->FetchField("md")) ,
 			'inc' =>sprintf("%.2f", $db->FetchField("inc")),
 			'azm' =>sprintf("%.2f", $db->FetchField("azm")),
 			'tvd' =>$db->FetchField("tvd"),
 			'vs' =>sprintf("%.2f", $db->FetchField("vs")),
 			'ns' =>sprintf("%.2f", $db->FetchField("ns")),
 			'ew' =>sprintf("%.2f", $db->FetchField("ew")),
 			'ca' =>sprintf("%.2f", $db->FetchField("ca")),
 			'cd' =>sprintf("%.2f", $db->FetchField("cd")),
 			'dl' =>sprintf("%.2f", $db->FetchField("dl")),
 			'cl' =>sprintf("%.2f", $db->FetchField("cl")),
 			'tcl'=>$db->FetchField("tot"),
 	        'tot'=>sprintf("%.2f",$tot),
 	        'bot'=>sprintf("%.2f",$bot),
 			'dip'=>sprintf("%.2f", $db->FetchField("dip")),
 			'fault'=>sprintf("%.2f", $db->FetchField("fault")),
 			'method'=>$db->FetchField("method"),
 	        'data'=>$db->FetchField("data")
 	);
 }
 echo json_encode($projections_joined);
 ?>