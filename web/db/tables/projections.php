<?php
if($gotproj==0) {
	logDoQuery($dbu, "
	CREATE TABLE projections
	(
		id serial NOT NULL,
		hide int NOT NULL DEFAULT 0,
		method int NOT NULL DEFAULT 0,
		data text NOT NULL DEFAULT '0,0,0',
		md float NOT NULL DEFAULT 0.0,
		inc float NOT NULL DEFAULT 0.0,
		azm float NOT NULL DEFAULT 0.0,
		tvd float NOT NULL DEFAULT 0.0,
		vs float NOT NULL DEFAULT 0.0,
		ns float NOT NULL DEFAULT 0.0,
		ew float NOT NULL DEFAULT 0.0,
		ca float NOT NULL DEFAULT 0.0,
		cd float NOT NULL DEFAULT 0.0,
		cl float NOT NULL DEFAULT 0.0,
		dl float NOT NULL DEFAULT 0.0,
		tot float NOT NULL DEFAULT 0.0,
		bot float NOT NULL DEFAULT 0.0,
		dip float NOT NULL DEFAULT 0.0,
		fault float NOT NULL DEFAULT 0.0,
		CONSTRAINT projections_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}

if(!$dbu->ColumnExists('projections', 'tf'))  
	logDoQuery($dbu, "alter table projections add tf text;");

if(!$dbu->ColumnExists('projections', 'ptype'))  
	logDoQuery($dbu, "alter table projections add ptype text default 'pa';");
?>