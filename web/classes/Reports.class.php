<?php
class Reports
{
    function __construct($request = '')
	{
    	$this->db=new dbio();
    	$this->params = $request; 
    }
    function generate_las_file($seldb){
    	$data = file_get_contents("file_templates/las_template.txt");
    	global $db,$wellname,$location,$field,$country,$county,$stateprov,$opname,$dirname,$wellid;
    	global $svy_total,$svy_plan,$svy_md,$svy_inc,$svy_azm,$svy_tvd,$svy_vs,$svy_ns,$svy_ew;
    	$start_depth=100;
    	$end_depth = 100;
    	$step ="1.00";
    	$survey_data="";
    	$log_data="";
    	$data = str_replace("{wellname}", str_pad($wellname,26," ",STR_PAD_LEFT), $data);
    	$data = str_replace("{loc}", str_pad($location,26," ",STR_PAD_LEFT), $data);
    	$data = str_replace("{field}", str_pad($field,26," ",STR_PAD_LEFT), $data);
    	$data = str_replace("{loc_country}", str_pad($country,26," ",STR_PAD_LEFT), $data);
    	$data = str_replace("{loc_county}", str_pad($county,26," ",STR_PAD_LEFT), $data);
    	$data = str_replace("{loc_state}", str_pad($stateprov,26," ",STR_PAD_LEFT), $data);
    	$data = str_replace("{ocomp}", str_pad($opname,26," ",STR_PAD_LEFT), $data);
    	$data = str_replace("{scomp}", str_pad($dirname,26," ",STR_PAD_LEFT), $data);
    	$data = str_replace("{uwi_num}", str_pad($wellid,26," ",STR_PAD_LEFT), $data);
    	$data = str_replace("{api_num}", str_pad($wellid,26," ",STR_PAD_LEFT), $data);
    	$data = str_replace("{date_now}",str_pad(date("m/d/Y"),26," ",STR_PAD_LEFT),$data);
		#TRACK   SVY      DEPTH    INCL      AZM       TVD        VS      N/-S       E/-W
		if($svy_plan[0]==1){
			$start_depth = $svy_md[2];
			$sinc = $svy_inc[2];
			$sazm = $svy_azm[2];
			$end_depth = $svy_md[1];
		} else {
			$start_depth = $svy_md[1];
			$sinc = $svy_inc[1];
			$sazm = $svy_azm[1];
			$end_depth = $svy_md[0];
		}		
		$data = str_replace("{srtdpth}", str_pad($start_depth,24," ",STR_PAD_LEFT), $data);
		$data = str_replace("{stpdpth}", str_pad($end_depth,24," ",STR_PAD_LEFT), $data);
		

		$data = str_replace("{stp}", str_pad($step,24," ",STR_PAD_LEFT), $data);
		$svy_cnt=0;
		//for ($i=($svy_total-1); $i>=0; $i--) {
		//	if($svy_plan[$i]==0){
		//		$survey_data.=str_pad($svy_md[$i],11," ",STR_PAD_LEFT).str_pad($svy_inc[$i],8," ",STR_PAD_LEFT)
		//		.str_pad($svy_azm[$i],9," ",STR_PAD_LEFT).str_pad(sprintf("%.2f",$svy_tvd[$i]),10," ",STR_PAD_LEFT)
		//		.str_pad($svy_vs[$i],10," ",STR_PAD_LEFT).str_pad($svy_ns[$i],10," ",STR_PAD_LEFT)
		//		.str_pad($svy_ew[$i],11," ",STR_PAD_LEFT)."\n";
		//		$svy_cnt++;
		//	}
		//}
		$rop_table_sql = "select * from edatalogs where label='ROP'";
		$db->DoQuery($rop_table_sql);
		$db->FetchRow();
		$rop_table_name = $db->FetchField("tablename");
		$log_sql ="select * from welllogs where startmd>=".$start_depth." and endmd <=".$end_depth;
		//$log_sql = "select edl_3.md,edl_3.vs,edl_3.tvd, edl_3.value as rop,eld_6.value as gr from edl_3 left join eld_6 on eld_6.md=edl_3.md where edl_3.md>=".$start_depth." and edl_3.md<=".$end_depth;
		
		$db->DoQuery($log_sql);
		$dbu = new dbio($seldb);
    	$dbu->OpenDb();
		#   DEPTH           GR        DEPTH           VS          INC          AZM          ROP
		#--------------------------------------------------------------------
		while($db->FetchRow()){
			$tablename= $db->FetchField("tablename");
			$sublog_sql = "select $tablename.md as md,$tablename.value as gr,$tablename.tvd as tvd,$tablename.vs as vs,$rop_table_name.value as rop from $tablename left join $rop_table_name on $rop_table_name.md=$tablename.md where $tablename.md >=".$start_depth." and $tablename.md<=".$end_depth;
			echo $sublog_sql."\n";
			$dbu->DoQuery($sublog_sql);
			while($dbu->FetchRow()){
				$log_data.= str_pad(sprintf("%.2f",$dbu->FetchField("md")),9," ",STR_PAD_LEFT).
				 str_pad(sprintf("%.2f",$dbu->FetchField("gr")),13," ",STR_PAD_LEFT).
				 str_pad(sprintf("%.2f",$dbu->FetchField("tvd")),13," ",STR_PAD_LEFT).
				 str_pad(sprintf("%.2f",$dbu->FetchField("vs")),13," ",STR_PAD_LEFT);
				 if($dbu->FetchField("md")==$end_depth){
				 	$log_data.=str_pad($sinc,13," ",STR_PAD_LEFT).
				 	str_pad($sazm,13," ",STR_PAD_LEFT);
				 } else {
					 $log_data.=str_pad("-9999.00",13," ",STR_PAD_LEFT).
				 	str_pad("-9999.00",13," ",STR_PAD_LEFT);
				 }
				 $log_data.=str_pad(sprintf("%.2f",$dbu->FetchField("rop")),13," ",STR_PAD_LEFT)."\r\n";
			}
		}
		$dbu->CloseDb();
		$filename="$wellname- ".$start_depth." - ".$end_depth." LAS.las";
		//$data = str_replace("{survey_data}",$survey_data,$data);
		$data = str_replace("{log_data}",$log_data,$data);
		file_put_contents("/tmp/$filename", $data);
		return "/tmp/$filename";
    }
    
    function generate_1inch($seldb){
    	require_once('/usr/share/php/fpdf/fpdf.php');
    }
    
    function generate_5inch($seldb){
    	require_once('/usr/share/php/fpdf/fpdf.php');
    }
    
    function user_access_list($seldb)
	{
    	$dbu = new dbio('sgta_index');
    	$dbu->OpenDb();
    	$query = "select * from server_info";
    	$dbu->DoQuery($query);
    	$row = $dbu->FetchRow();
    	if($row['on_lan']){
    		$access_url = $row['reports_lan'];
    		$selserver = $row['lan_addr'];
    	}else{
    		$access_url = $row['reports_wan'];
    		$selserver = $row['wan_addr'];
    	}
    	$access_url = "https://".$access_url."/reporting/user_access_list.php?seldbname=$seldb&selserver=$selserver";
		//echo '<pre>'; print_r($row); echo '</pre>';
		//echo "<p>on_lan=" . ($row['on_lan'] ? 'true' : 'false') . " access_url=$access_url</p>";
		//exit();
    	$process = curl_init($access_url);
		curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($process, CURLOPT_HEADER, 1);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		$return = curl_exec($process);
		curl_close($process);
		$pos = strpos($return,"\r\n\r\n");
		$resp = substr ($return,$pos);
 		return json_decode($resp);	
    }
    
    function report_list()
	{
	 	$dbu = new dbio($this->params['seldbname']);
	 	$dbu->OpenDb();
	 	$query = "select * from reports order by id desc";
	 	$dbu->DoQuery($query);
	 	$retar= array();
	 	while($row = $dbu->FetchRow()){
	 		array_push($retar,$row); 
	 	}
	 	return $retar;		
   }
}
?>
