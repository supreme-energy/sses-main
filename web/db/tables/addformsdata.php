<?php
if($gotaddformsdata==1 && !$dbu->ColumnExists('addformsdata', 'thickness')) {
	logDoQuery($dbu, "DROP TABLE addformsdata;");
	$gotaddformsdata=0;
}
if($gotaddformsdata==0) {
	logDoQuery($dbu, "
	CREATE TABLE addformsdata
	(
		id serial NOT NULL,
		infoid int NOT NULL DEFAULT -1,
		svyid int NOT NULL DEFAULT -1,
		projid int NOT NULL DEFAULT -1,
		md float NOT NULL DEFAULT 0,
		tvd float NOT NULL DEFAULT 0,
		vs float NOT NULL DEFAULT 0,
		dip float NOT NULL DEFAULT 0,
		fault float NOT NULL DEFAULT 0,
		tot float NOT NULL DEFAULT 0,
		bot float NOT NULL DEFAULT 0,
		thickness float NOT NULL DEFAULT 0,
		CONSTRAINT addformsdata_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}
?>