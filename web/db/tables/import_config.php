<?php
if($import_config == 0) {
	logDoQuery($dbu,"CREATE TABLE import_config (
    id integer NOT NULL,
    field_name text,
    field_value TEXT,
	field_column_index smallint)");
	logDoQuery($dbu,"CREATE SEQUENCE import_config_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");
	logDoQuery($dbu,"ALTER TABLE ONLY import_config ALTER COLUMN id SET DEFAULT nextval('import_config_id_seq'::regclass)");
	logDoQuery($dbu,"ALTER TABLE ONLY import_config
    ADD CONSTRAINT import_config_pkey PRIMARY KEY (id)");
}
?>