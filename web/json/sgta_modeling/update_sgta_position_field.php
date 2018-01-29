<?php 
	$field = $_REQUEST['field'];
	$value = $_REQUEST['value'];
	if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "select count(*) as cnt from splotlist WHERE ptype='SGTA' AND mtype='DEPTH' ";
	$db->DoQuery($query);
	if($db->FetchRow()){
		if($db->FetchField('cnt') <= 0){
			$query = "insert into splotlist (ptype,mtype,inputa,inputb,mintvd,maxtvd,minvs,maxvs) values ('SGTA','DEPTH',0,0,0,0,0,0)";
			$db->DoQuery($query);
		}
	} 
	$query = "update splotlist set $field = '$value' WHERE ptype='SGTA' AND mtype='DEPTH';";
	echo $query;
	$db->DoQuery($query);
	$db->CloseDb();
	echo 'Done';
?>