<?php
if($gotwitsml_log==0){
	logDoQuery($dbu, "
	CREATE TABLE witsml_log
	(
		id serial NOT NULL,
		type text NOT NULL DEFAULT '',
		witsml text NOT NULL DEFAULT '',
		uid text NOT NULL DEFAULT '',
		sent_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		CONSTRAINT witsml_log_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");	
}
if(!$dbu->ColumnExists('witsml_details', 'wellid'))  
	logDoQuery($dbu, "alter table witsml_details add wellid text;");

if(!$dbu->ColumnExists('witsml_details', 'boreid'))  
	logDoQuery($dbu, "alter table witsml_details add boreid text;");

if(!$dbu->ColumnExists('witsml_details', 'trajid'))  
	logDoQuery($dbu, "alter table witsml_details add trajid text;");

if(!$dbu->ColumnExists("witsml_details","logid")){
	logDoQuery($dbu,"alter table witsml_details add logid text;");
}
?>