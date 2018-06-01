<?php
if($nogo_point==0){
	logDoQuery($dbu,"CREATE TABLE nogo_point (
    id integer NOT NULL,
    vs double precision,
    tvd double precision,
    label TEXT DEFAULT '' NOT NULL,
    show_plot smallint DEFAULT 0 NOT NULL,
    show_report smallint DEFAULT 0 NOT NULL)");
	logDoQuery($dbu,"CREATE SEQUENCE nogo_point_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");
	logDoQuery($dbu,"ALTER TABLE ONLY nogo_point ALTER COLUMN id SET DEFAULT nextval('nogo_point_id_seq'::regclass)");
	logDoQuery($dbu,"ALTER TABLE ONLY nogo_point
    ADD CONSTRAINT nogo_point_pkey PRIMARY KEY (id)");
}
?>