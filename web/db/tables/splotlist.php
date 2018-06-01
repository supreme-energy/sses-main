<?php
if($gotplist==0) {
	logDoQuery($dbu, "
	CREATE TABLE splotlist
	(
		id serial NOT NULL,
		ptype text NOT NULL DEFAULT '',
		mtype text NOT NULL DEFAULT '',
		inputa float NOT NULL DEFAULT 5.0,
		inputb float NOT NULL DEFAULT 6.0,
		CONSTRAINT splotlist_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}

if(!$dbu->ColumnExists('splotlist', 'mintvd'))
	logDoQuery($dbu, "alter table splotlist add mintvd float not null default 99999.0;");
if(!$dbu->ColumnExists('splotlist', 'maxtvd'))
	logDoQuery($dbu, "alter table splotlist add maxtvd float not null default -99999.0;");
if(!$dbu->ColumnExists('splotlist', 'minvs'))
	logDoQuery($dbu, "alter table splotlist add minvs float not null default 99999.0;");
if(!$dbu->ColumnExists('splotlist', 'maxvs'))
	logDoQuery($dbu, "alter table splotlist add maxvs float not null default -99999.0;");
?>