
<?php
require_once("dbio.class.php");
$db=new dbio("sgta_index");
$db->OpenDb();
echo "\ndbupdate: Checking database $newdbname for updates...\n";
if(!$db->ColumnExists('dbindex', 'favorite')) {
	$query = "alter table dbindex add favorite integer not null default 0;";
	echo("altering with: ".$query);
	$db->DoQuery($query);
}

?>