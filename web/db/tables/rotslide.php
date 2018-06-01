<?php
if($rotslide == 0)
{
	logDoQuery($dbu,"create table rotslide (" .
		"rsid integer not null," .
		"rotstartmd numeric default 0.0 not null," .
		"rotendmd numeric default 0.0 not null," .
		"slidestartmd numeric default 0.0 not null," .
		"slideendmd numeric default 0.0 not null," .
		"tfo text default '' not null," .
		"rotstartvs numeric default 0.0 not null," .
		"rotendvs numeric default 0.0 not null," .
		"slidestartvs numeric default 0.0 not null," .
		"slideendvs numeric default 0.0 not null," .
		"updated_dt timestamp without time zone default now() not null" .
		") with (OIDS=FALSE)");
	logDoQuery($dbu,"CREATE SEQUENCE rotslide_rsid_seq START WITH 1 INCREMENT BY 1 " .
		"NO MINVALUE NO MAXVALUE CACHE 1");
	logDoQuery($dbu,"ALTER SEQUENCE rotslide_rsid_seq OWNED BY rotslide.rsid");
	logDoQuery($dbu,"ALTER TABLE ONLY rotslide ALTER COLUMN rsid " .
		"SET DEFAULT NEXTVAL('rotslide_rsid_seq'::regclass)");
	logDoQuery($dbu,"ALTER TABLE ONLY rotslide ADD CONSTRAINT rotslide_pkey PRIMARY KEY (rsid)");
}
if(!$dbu->ColumnExists('rotslide', 'md')) {
	logDoQuery($dbu,"alter table rotslide add md varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('rotslide', 'bur')) {
	logDoQuery($dbu,"alter table rotslide add bur varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('rotslide', 'turn_rate')) {
	logDoQuery($dbu,"alter table rotslide add turn_rate varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('rotslide', 'motor_yield')) {
	logDoQuery($dbu,"alter table rotslide add motor_yield varchar(32) not null default ''");
}
?>