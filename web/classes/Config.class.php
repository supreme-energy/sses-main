<?php
class Config {

    function __construct($request) {
    	$this->db_name = $request['seldbname'];
    	$this->db=new dbio("$this->db_name"); 
    	$this->_raw=null;
    	if($this->query){
    		if(!is_array($this->query)){
    			$this->query = array($this->query);
    		}
    		$this->db->OpenDb();
    		foreach($this->query as $query){
	    		$this->db->DoQuery($query);
	    		$row = $this->db->FetchRow();
	    		if($row){
	    			if($this->_raw){
	    				$this->_raw =array_merge($this->_raw,$row);
	    			} else {
	    				$this->_raw=$row;
	    			} 
	    			
	    			foreach($row as $key=>$val){
	    					$this->$key=$val;
	    			}
	    		}
    		}	
    	}
    	$this->db->CloseDb();
    }
    function to_json(){
    	return json_encode(array($this->_raw));
    }
    function as_array(){
    	return $this->_raw;
    }
}
?>