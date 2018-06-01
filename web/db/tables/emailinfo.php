<?php

if($gotinfo==0) {
	logDoQuery($dbu, "
	CREATE TABLE emailinfo
	(
		id serial NOT NULL,
		smtp_from text NOT NULL DEFAULT '',
		smtp_server text NOT NULL DEFAULT '',
		smtp_login text NOT NULL DEFAULT '',
		smtp_password text NOT NULL DEFAULT '',
		enabled integer NOT NULL DEFAULT 1,
		CONSTRAINT emailinfo_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
	logDoQuery($dbu, "INSERT INTO emailinfo (smtp_from) VALUES ('email@yourdomain.com');");
}

if(!$dbu->ColumnExists('emailinfo', 'smtp_message'))
	logDoQuery($dbu, "alter table emailinfo add smtp_message text not null default 'Email report';");

?>