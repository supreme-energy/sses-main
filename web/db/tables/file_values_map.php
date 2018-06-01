<?php
if($file_values_map == 0){
    logDoQuery($dbu,"CREATE TABLE file_values_map (
    id integer NOT NULL,
    imported_file_id integer,
    source_table TEXT,
    source_column TEXT,
    value_type TEXT,
    columns TEXT,
    rows TEXT
	)");
	logDoQuery($dbu,"CREATE SEQUENCE imported_files_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");
}
?>