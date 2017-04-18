<?php
require_once ("../dbio.class.php");
class WellLog {
	function __construct($request) {
		$this->welllogs=array();
		$this->db_name = $request['seldbname'];
    	$this->db=new dbio("$this->db_name"); 
    	$query = "select * from welllogs order by endmd";
    	$this->db->OpenDb();
    	$this->db->DoQuery($query);
    	while($row=$this->db->FetchRow()){
    		array_push($this->welllogs,$row);
    	}
    	$this->db->CloseDb();
	}
	
	function get_tvdtogamma($startdepth=0,$enddepth=999999,$incr){
		if($incr){
			$sample_startdepth=$startdepth-($incr*5);
			$sample_enddepth = $enddepth+($incr*5);
		} else {
			$sample_startdepth=$startdepth;
			$sample_enddepth = $enddepth;
		}
		$results = array();
		$sortar = array();
		$sortar_inc = array();
		$this->db->OpenDb();
		$plotbias=0;
		$plotscale=0;
		$plotdip=0;
		$plotfault=0;
		$first=true;
		$incr_approved=true;
		$last_depth=0;
		$current_depth = $startdepth;
		$row1=null;
		$row2=null;
		$interpol_p1_depth=0;
		$interpol_p1_gamma=0;
		$interpol_p2_depth=999999;
		$interpol_p2_gamma=0;
		$lpcnt=0;
		$inc_array=array();
		
		foreach($this->welllogs as $log){
			$table = $log['tablename'];
			$scale = $log['scalefactor'];
			$bias  = $log['scalebias'];		
			$query = "select depth,value as gamma  from $table where depth>$sample_startdepth and depth<$sample_enddepth";
			$this->db->DoQuery($query);
			if($incr){
				while($row_obj = $this->db->FetchRow()){
					$gamma = $row_obj['gamma'];
					$row_obj['tvd']=floatval($row_obj['depth']);
					$gamma *= $scale;
					$gamma += $bias;
					$row_obj['gamma']=$gamma;
					array_push($inc_array,$row_obj);
				}
			}else{
				while($row=$this->db->FetchRow()){
					
					$gamma = $row['gamma'];
					$row['tvd']=floatval($row['depth']);
					
					$gamma *= $scale;
					$gamma += $bias;
					$row['gamma']=$gamma;
					$depth_change = $row['tvd']-$last_depth;
					$incr_approved=true;
					
					if($first || $incr_approved){
						array_push($results,$row);
						$last_depth=$row['depth'];
					}			
					$first=false;
				}
			}
		}
		if($incr){
			foreach($inc_array as $key=>$row){
				$sortar_inc[$key]=$row['tvd'];
			}
			array_multisort($sortar_inc,SORT_ASC,$inc_array);
			while($current_depth < $inc_array[0]['tvd']){
				$current_depth+=$incr;
			}
			while($current_depth<=$enddepth){
					if(!$row1){
						$row1= array_shift($inc_array);
					}	
					if(!$row2){
						$row2=array_shift($inc_array);
					}
					while($row2['tvd']<$current_depth){
						$row1=$row2;
						$row2=array_shift($inc_array);
						if(!$row2){
							break;
						}
					}
					if(!$row2){
						break;
					}
					
									
					$interpol_p1_depth=$row1['tvd'];
					$interpol_p1_gamma=$row1['gamma'];
						
					$interpol_p2_depth=$row2['tvd'];
					$interpol_p2_gamma=$row2['gamma'];
						
					
					$idepth = $current_depth;
					$igamma = $interpol_p1_gamma+($interpol_p2_gamma-$interpol_p1_gamma)*(($current_depth-$interpol_p1_depth)/($interpol_p2_depth-$interpol_p1_depth));
					$row=array();
					$row['tvd']=$idepth;
					$row['gamma']=$igamma;
					array_push($results,$row);
					$current_depth+=$incr;
				}
		}
		$this->db->CloseDb();
		foreach($results as $key=>$row){
			$sortar[$key]=$row['tvd'];
		}
		array_multisort($sortar,SORT_ASC,$results);
		return $results;
	}
}
?>