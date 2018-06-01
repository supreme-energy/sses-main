<?php
if($gotlist==0) {
	logDoQuery($dbu, "
	CREATE TABLE emaillist
	(
		id serial NOT NULL,
		name text NOT NULL DEFAULT '',
		email text NOT NULL DEFAULT '',
		enabled integer NOT NULL DEFAULT 1,
		CONSTRAINT emaillist_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}

if(!$dbu->ColumnExists('emaillist', 'phone'))
	logDoQuery($dbu, "alter table emaillist add phone text not null default '';");
if(!$dbu->ColumnExists('emaillist', 'cat'))
	logDoQuery($dbu, "alter table emaillist add cat text not null default 'Operator';");


if(!$dbu->ColumnExists('emaillist', 'las_file')) {
	logDoQuery($dbu,"alter table emaillist add las_file int default 0;");
}

if(!$dbu->ColumnExists('emaillist', 'report_1')) {
	logDoQuery($dbu,"alter table emaillist add report_1 int default 0;");
}

if(!$dbu->ColumnExists('emaillist', 'report_2')) {
	logDoQuery($dbu,"alter table emaillist add report_2 int default 0;");
}
?>