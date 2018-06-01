<?php
if($ghostdata==0){
	logDoQuery($dbu,"CREATE TABLE ghost_data (
    id integer NOT NULL,
    md double precision,
    tvd double precision,
    vs double precision,
    value double precision,
    hide smallint DEFAULT 0 NOT NULL,
    depth double precision DEFAULT 0 NOT NULL)");
	logDoQuery($dbu,"CREATE SEQUENCE ghost_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");
	logDoQuery($dbu,"ALTER TABLE ONLY ghost_data ALTER COLUMN id SET DEFAULT nextval('ghost_data_id_seq'::regclass)");
	logDoQuery($dbu,"ALTER TABLE ONLY ghost_data
    ADD CONSTRAINT ghost_data_pkey PRIMARY KEY (id)");
}
?>