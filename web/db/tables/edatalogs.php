<?php
if($gotedatalogs==0) {
	logDoQuery($dbu, "
	CREATE TABLE edatalogs
	(
		id serial NOT NULL,
		colnum int NOT NULL DEFAULT 0,
		tablename text NOT NULL DEFAULT '',
		label text NOT NULL DEFAULT 'edata',
		scalelo float NOT NULL DEFAULT 0.0,
		scalehi float NOT NULL DEFAULT 300.0,
		enabled integer NOT NULL DEFAULT 0,
		logscale integer NOT NULL DEFAULT 0,
		color text NOT NULL DEFAULT '#0000ff',
		CONSTRAINT edatalogs_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}
if(!$dbu->ColumnExists('edatalogs', 'single_plot')) {
	logDoQuery($dbu,"alter table edatalogs add single_plot int not null default 0");
}
?>