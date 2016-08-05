<?
	require_once("dbio.class.php");
	$dbname= $_REQUEST['seldbname'];
	$dbserver = $_REQUEST['selserver'];
	$dbu=new dbio();
	$dbu->OpenDb();
	$query = "select wan_address,lan_address from servers where wan_address='$dbserver' or lan_address='$dbserver'";
	$dbu->DoQuery($query);
	$row = $dbu->FetchRow();
	$waddr = $row['wan_address'];
	$laddr = $row['lan_address'];
	$query = "select username,dbserver,dbname from user_tdbas ut left join users u on u.id=ut.user_id where dbname='$dbname' and (dbserver='$waddr' or dbserver='$laddr')";
	$dbu->DoQuery($query);
	$results = array();
	while($row =$dbu->FetchRow()){
		$results[$row['username']]=$row;
	}
	echo json_encode($results);
?>