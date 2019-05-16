 <?php
 include("api_header.php");
 $db2=new dbio($seldbname);
 $db2->OpenDb();
 $db->DoQuery("select * from addforms");
 $totid =null;
 $botid = null;
 while($db->FetchRow()){
 
 	if(trim($db->FetchField('label'))=='TOT'){
 		$totid = $db->FetchField('id');
 	}
 	if(trim($db->FetchField('label'))=='BOT'){
 		$botid = $db->FetchField('id');
 	}
 }
 $projections_joined = array();
 $db->DoQuery("SELECT * FROM projections ORDER BY md $surveysort;");
 while($db->FetchRow()){
 	$svyid = $db->FetchField("id");
 	if($totid){
 		
 		$query = "select tot from addformsdata where projid=$svyid and infoid=$totid;";
 		
 		$db2->DoQuery($query);
 		$db2->FetchRow();
 		$tot =sprintf("%.2f", $db2->FetchField("tot"));
 	}
 	if($botid){ 
 		$query = "select tot from addformsdata where projid=$svyid and infoid=$botid;";
 		$db2->DoQuery($query);
 		$db2->FetchRow();
 		$bot =sprintf("%.2f", $db2->FetchField("tot"));
 	}
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
 			'tot'=>$tot,
 			'bot'=>$bot,
 			'dip'=>sprintf("%.2f", $db->FetchField("dip")),
 			'fault'=>sprintf("%.2f", $db->FetchField("fault")),
 			'method'=>$db->FetchField("method")
 	);
 }
 echo json_encode($projections_joined);
 ?>