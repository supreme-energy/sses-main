<?php
if($ghostprojects==0){
	logDoQuery($dbu,"CREATE TABLE ghost_projects (
    id integer NOT NULL,
    hide integer DEFAULT 0 NOT NULL,
    method integer DEFAULT 0 NOT NULL,
    data text DEFAULT '0,0,0'::text NOT NULL,
    md double precision DEFAULT 0.0 NOT NULL,
    inc double precision DEFAULT 0.0 NOT NULL,
    azm double precision DEFAULT 0.0 NOT NULL,
    tvd double precision DEFAULT 0.0 NOT NULL,
    vs double precision DEFAULT 0.0 NOT NULL,
    ns double precision DEFAULT 0.0 NOT NULL,
    ew double precision DEFAULT 0.0 NOT NULL,
    ca double precision DEFAULT 0.0 NOT NULL,
    cd double precision DEFAULT 0.0 NOT NULL,
    cl double precision DEFAULT 0.0 NOT NULL,
    dl double precision DEFAULT 0.0 NOT NULL,
    tot double precision DEFAULT 0.0 NOT NULL,
    bot double precision DEFAULT 0.0 NOT NULL,
    dip double precision DEFAULT 0.0 NOT NULL,
    fault double precision DEFAULT 0.0 NOT NULL,
    tf text,
    ptype text DEFAULT 'pa'::text
)");
logDoQuery($dbu,"CREATE SEQUENCE ghost_projects_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");


logDoQuery($dbu,"ALTER SEQUENCE ghost_projects_id_seq OWNED BY gost_projects.id");
logDoQuery($dbu,"ALTER TABLE ONLY ghost_projects ALTER COLUMN id SET DEFAULT nextval('ghost_projects_id_seq'::regclass)");
logDoQuery($dbu,"ALTER TABLE ONLY ghost_projects ADD CONSTRAINT ghost_projects_pkey PRIMARY KEY (id)");
}
?>