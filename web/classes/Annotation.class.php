<?php

require_once("witsml_encode.php");
class Annotation {
    function __construct($request) {
    	$this->my_settings=null;
    	$this->db_name = $request['seldbname'];
    	$this->db=new dbio("$this->db_name"); 
    }
    
   function showCol($colname){
   	if(!$this->my_settings){
   		$this->db->OpenDb();
   		$this->db->DoQuery("select anno_settings from appinfo");
   		$this->db->FetchRow();
   		$anno_settings = explode('|',$this->db->FetchField('anno_settings'));
   		$anno_sf = array('md'=>false,'inc'=>false,'azm'=>false,'avg_dip'=>false,'avg_gas'=>false,'avg_rop'=>false,'in_zone'=>false);
		foreach($anno_settings as $aset){
			$s = explode(':',$aset);
		
			$anno_sf[$s[0]]= ($s[1]=='1'?true:false);
		}
		$this->my_settings=$anno_sf;
   	}
   	return $this->my_settings[$colname];
   } 
   function delete($aid){
   		$this->db->OpenDb();
   		$this->db->DoQuery("delete from annos where id='$aid'");
   }
   function create($sid,$settime,$settings){
   		$this->db->OpenDb();
    	$this->db->DoQuery("insert into annos (assigned_date,detail_assignments,survey_id) values ('$settime','$settings',$sid)");
   }
   
   function get_all($as='json'){
   	$this->db->OpenDb();
   	$this->db->DoQuery("select s.md,s.inc,s.azm,a.* from annos a left join surveys s on s.id=a.survey_id order by s.md asc");
   	$rows = array();
   	while($row = $this->db->FetchRow()){
   		array_push($rows,$row);	
   	}
   	if($as=='json'){
   		return json_encode($rows);
   	}else{
   		return $rows;
   	}
	}
	function get_avgs($start_depth,$end_depth){
		if(!$start_depth) { $start_depth=0; }
		if(!$end_depth) { $end_depth=0; }
   	if($start_depth >=0 && $end_depth >=0){
	   	$this->db->OpenDb();
	   	$this->db->DoQuery("select avg(dip) as adip from surveys where md<=$end_depth and md>$start_depth");
	   	$row = $this->db->FetchRow();
	   	//avg dip
	   	$adip=$row['adip'];
	   	//avg gas
	   	$this->db->DoQuery('select tablename from edatalogs where label=\'GAS\'');
	   	if($row=$this->db->FetchRow()){
	   		$tn = $row['tablename'];
	   		$this->db->DoQuery("select avg(value) as agas from $tn where md<=$end_depth and md>$start_depth and value >-9999.0");
	   		$row2= $this->db->FetchRow();
	   		$agas=$row2['agas'];
	   	}else{
	   		$agas='N/A';
	   	}
	   	
	   	//avg rop
	   	$this->db->DoQuery('select tablename from edatalogs where label=\'ROP\'');
	   	if($row=$this->db->FetchRow()){
	   		$tn = $row['tablename'];
	   		$this->db->DoQuery("select avg(value) as arop from $tn where md<=$end_depth and md>$start_depth and value >-9999.0");
	   		$row2= $this->db->FetchRow();
	   		$arop=$row2['arop'];
	   	}else{
	   		$arop='N/A';
	   	}
	   	//footage
	   	$this->db->DoQuery("select sum(cl) as sumcl from surveys where md<=$end_depth and md >$start_depth ");
	   	if($row=$this->db->FetchRow()){
	   		$foot = $row['sumcl'];
	   	} else {
	   		$foot = 'NVP';
	   	}
	   	return array('gas'=>$agas,'rop'=>$arop,'dip'=>$adip,'footage'=>$foot);
   	} else {
   		return null;
   	}
   }
   function get($id){}
    
}
?>
