<?php
if($file_values_map == 0){
    logDoQuery($dbu,"CREATE TABLE file_values_map (
    id integer NOT NULL,
    imported_file_name TEXT,
    source_table TEXT,
    source_column TEXT,
    value_type TEXT,
    columns TEXT,
    rows TEXT
	)");
	logDoQuery($dbu,"CREATE SEQUENCE file_values_map_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");
	logDoQuery($dbu,"ALTER TABLE ONLY file_values_map ALTER COLUMN id SET DEFAULT nextval('file_values_map_id_seq'::regclass)");
	logDoQuery($dbu,"ALTER TABLE ONLY file_values_map
    ADD CONSTRAINT file_values_map_pkey PRIMARY KEY (id);");
}
?>