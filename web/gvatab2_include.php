<?php 
$cldip=0.0;
$stregdipazm=111.00;
$db->DoQuery("SELECT * FROM controllogs ORDER BY tablename");
if ($db->FetchRow()) {
	$tablename=$db->FetchField("tablename");
	$startmd=$db->FetchField("startmd");
	$endmd=$db->FetchField("endmd");
	$cltot=$db->FetchField("tot");
	$clbot=$db->FetchField("bot");
	$cldip=$db->FetchField("dip");
	$stregdipazm=$db->FetchField("azm");
}
else {
	// create an entry in the controllogs table
	$db->DoQuery("INSERT INTO controllogs (tablename) VALUES ('xxxxxx');");
	$db->DoQuery("SELECT * FROM controllogs WHERE tablename='xxxxxx';");
	$id="";
	if($db->FetchRow())
		$id = $db->FetchField("id");
	// create table which contains imported data
	if($id!="") {
		$tablename="cld_$id";
		$query="CREATE TABLE \"$tablename\" (id serial not null, md float, tvd float, vs float, value float, hide smallint not null default 0);";
		$db->DoQuery($query);
		$query="UPDATE controllogs SET tablename='$tablename' WHERE id='$id';";
		$db->DoQuery($query);
	}
	$startmd=0;
	$endmd=100;
}
?>