<?php
require_once("dbio.class.php");
$seldbname = $_REQUEST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
$uniqid = uniqid();
$collision_name = isset($_REQUEST['cn'])?$_REQUEST['cn']:'new anticollsion '.$uniqid;
$table_name = "ac_well_survey_".$uniqid;
$sql = "insert into anticollision_wells (tablename,realname) values ('$table_name','$collision_name')  RETURNING id";
$db->DoQuery($sql);
$db->FetchRow();
$acid = $db->FetchField('id');
$sql = "create table $table_name (id serial not null," .
		"md numeric," .
		"inc numeric," .
		"azm numeric," .
		"tvd numeric," .
		"vs numeric," .
		"ca numeric," .
		"cd numeric," .
		"dl numeric," .
		"ew numeric," .
		"ns numeric," .
		"cl double precision not null default 0," .
		"constraint ".$table_name."_pkey PRIMARY KEY(id)" .
		") WITH(OIDS=FALSE)";
$db->DoQuery($sql);
$db->CloseDb();
header("Location: anticollisionwells.php?seldbname=$seldbname&acwellid=$acid");
?>