<?php
require_once("../dbio.class.php");
class Index {
    function __construct() {
    	$this->db=new dbio("sgta_index");
    }
    
    function get_jobs($as_json=true){
    	$this->db->OpenDb();
    	$this->db->DoQuery("SELECT * FROM dbindex ORDER BY id DESC;");
    	$rows =array();
    	while($row = $this->db->FetchRow()){
    		array_push($rows,$row);
    	}
    	$this->db->CloseDb();
    	if($as_json){
    		return json_encode($rows);
    	} else {
    		return $rows;
    	}
    }
}
?>