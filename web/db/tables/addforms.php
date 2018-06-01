<?php
if($gotaddforms==0) {
	logDoQuery($dbu, "
	CREATE TABLE addforms
	(
		id serial NOT NULL,
		label text NOT NULL DEFAULT '',
		color text NOT NULL DEFAULT '0000ff',
		selected int NOT NULL DEFAULT 0,
		md float NOT NULL DEFAULT 0,
		tvd float NOT NULL DEFAULT 0,
		vs float NOT NULL DEFAULT 0,
		dip float NOT NULL DEFAULT 0,
		fault float NOT NULL DEFAULT 0,
		tot float NOT NULL DEFAULT 0,
		bot float NOT NULL DEFAULT 0,
		thickness float NOT NULL DEFAULT 0,
		CONSTRAINT addforms_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}

if(!$dbu->ColumnExists('addforms', 'bg_color')) {
	logDoQuery($dbu,"alter table addforms add bg_color varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('addforms', 'bg_percent')) {
	logDoQuery($dbu,"alter table addforms add bg_percent double precision not null default 0.0");
}

if(!$dbu->ColumnExists('addforms', 'pat_color')) {
	logDoQuery($dbu,"alter table addforms add pat_color varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('addforms', 'pat_num')) {
	logDoQuery($dbu,"alter table addforms add pat_num int not null default 0");
}

if(!$dbu->ColumnExists('addforms', 'show_line')) {
	logDoQuery($dbu,"alter table addforms add show_line varchar(8) not null default 'Yes'");
}
?>