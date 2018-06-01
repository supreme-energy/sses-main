<?php
if($imported_files == 0){
    logDoQuery($dbu,"CREATE TABLE imported_files (
    id integer NOT NULL,
    filename text,
    filepath TEXT
	)");
	logDoQuery($dbu,"CREATE SEQUENCE imported_files_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");
}
?>