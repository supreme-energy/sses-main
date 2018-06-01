<?php
if($nogo_zone==0){
	logDoQuery($dbu,"CREATE TABLE nogo_zone (
    id integer NOT NULL,
    minvs double precision,
    mintvd double precision,
    maxvs double precision,
    maxtvd double precision,
    show_plot smallint DEFAULT 0 NOT NULL,
    show_report smallint DEFAULT 0 NOT NULL)");
	logDoQuery($dbu,"CREATE SEQUENCE nogo_zone_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");
	logDoQuery($dbu,"ALTER TABLE ONLY nogo_zone ALTER COLUMN id SET DEFAULT nextval('nogo_zone_id_seq'::regclass)");
	logDoQuery($dbu,"ALTER TABLE ONLY nogo_zone
    ADD CONSTRAINT nogo_zone_pkey PRIMARY KEY (id)");
}
?>