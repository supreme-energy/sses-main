<?php
if($gorigminder_con==0){
	logDoQuery($dbu, "
	CREATE TABLE rigminder_connection
	(
		id serial NOT NULL,
		host text NOT NULL DEFAULT '',
		username text NOT NULL DEFAULT '',
		password text NOT NULL DEFAULT '',
		dbname text NOT NULL DEFAULT '',
		data_transfer boolean NOT NULL DEFAULT false,
		CONSTRAINT rgc_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}

if(!$dbu->ColumnExists('rigminder_connection', 'aisd'))  
	logDoQuery($dbu, "alter table rigminder_connection add aisd float default 0.0;");

if(!$dbu->ColumnExists("rigminder_connection","connection_type")){
	logDoQuery($dbu,"alter table rigminder_connection add connection_type text;");
}?>