<?php
if($ghostsurveys==0){
	logDoQuery($dbu,"CREATE TABLE ghost_surveys (
    azm numeric,
    dl numeric,
    ew numeric,
    id integer NOT NULL,
    inc numeric,
    md numeric,
    ns numeric,
    temp numeric,
    tvd numeric,
    vs numeric,
    ca numeric,
    cd numeric,
    hide integer DEFAULT 0 NOT NULL,
    plan integer DEFAULT 0 NOT NULL,
    cl double precision DEFAULT 0 NOT NULL,
    tot double precision DEFAULT 0 NOT NULL,
    bot double precision DEFAULT 0 NOT NULL,
    dip double precision DEFAULT 0 NOT NULL,
    fault double precision DEFAULT 0 NOT NULL,
    srcts double precision,
    new boolean DEFAULT true
	)");
	logDoQuery($dbu,"CREATE SEQUENCE ghost_surveys_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");	
    logDoQuery($dbu,"ALTER TABLE ONLY ghost_surveys ALTER COLUMN id SET DEFAULT nextval('ghost_surveys_id_seq'::regclass)");
    logDoQuery($dbu,"ALTER TABLE ONLY ghost_surveys
    ADD CONSTRAINT ghost_surveys_pkey PRIMARY KEY (id);");
}

?>