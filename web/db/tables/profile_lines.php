<?php
if($profile_lines==0){
	logDoQuery($dbu,"CREATE TABLE profile_lines (
    id integer NOT NULL,
    color text,
    reference_database TEXT DEFAULT '' NOT NULL,
    label TEXT DEFAULT '' NOT NULL,
    show_plot smallint DEFAULT 0 NOT NULL,
    show_report smallint DEFAULT 0 NOT NULL)");
	logDoQuery($dbu,"CREATE SEQUENCE profile_lines_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");
	logDoQuery($dbu,"ALTER TABLE ONLY profile_lines ALTER COLUMN id SET DEFAULT nextval('profile_lines_id_seq'::regclass)");
	logDoQuery($dbu,"ALTER TABLE ONLY profile_lines
    ADD CONSTRAINT profile_lines_pkey PRIMARY KEY (id)");
}
if(!$dbu->ColumnExists('profile_lines', 'pattern')) {
	logDoQuery($dbu,"alter table profile_lines add pattern text default '0'");
}

?>