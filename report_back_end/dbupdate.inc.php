<?
$response="";
function logDoQuery($dbp, $s) {
	$dbp->DoQuery($s);
}
$dbu=new dbio();
$dbu->OpenDb();
$dbu->DoQuery("VACUUM ANALYZE;");
// logInfo("dbupdate: Checking database $seldbname for updates...");

$users=0;
$tokens=0;
$user_tdba =0;
$servers = 0;
$config = 0;
$dbu->DoQuery("SHOW TABLES");
while($dbu->FetchRow()) {
	$tn=$dbu->FetchField("tablename");
	if($tn=="users")	$users=1;
	if($tn=="user_tdbas")	$user_tdba=1;
	if($tn=="tokens")	$tokens=1;
	if($tn=="config")	$config=1;
	if($tn=="servers")	$servers=1;
}
if($config==0){
	logDoQuery($dbu,"create table config".
	"(" .
			"id serial not null," .
			"config_name text not null," .
			"config_value text not null," .
			"constraint config_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");
}
if($servers==0){
	logDoQuery($dbu,"create table servers".
	"(" .
			"id serial not null," .
			"wan_address text not null," .
			"lan_address text not null," .
			"server_name text not null," .
			"constraint servers_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");
}
if($users==0){
	logDoQuery($dbu,"create table users".
	"(" .
			"id serial not null," .
			"created timestamp not null default now()," .
			"username text not null," .
			"password text not null," .
			"constraint users_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");
}
if($user_tdba==0){
	logDoQuery($dbu,"create table user_tdbas" .
			"(" .
			"id serial not null," .
			"created timestamp not null default now()," .
			"dbname text not null," .
			"dbid text not null," .
			"dbserver text not null," .
			"dbserver_uname text not null," .
			"dbserver_pass text not null," .
			"user_id int not null," .
			"constraint user_tdbas_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");	
}
if($tokens==0){
	logDoQuery($dbu,"create table tokens" .
			" (" .
			"id serial not null," .
			"created timestamp not null default now(),".
			"token text not null,".
			"user_id int not null,".
			"expired int not null default 0,".
			"constraint token_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");
}

$dbu->CloseDb();
?>