<?php
if($admconfig == 0)
{
	logDoQuery($dbu,"CREATE TABLE adm_config (" .
		"confid INTEGER NOT NULL," .
		"cname TEXT DEFAULT '' NOT NULL," .
		"cvalue TEXT DEFAULT '' NOT NULL," .
		"cdesc TEXT DEFAULT '' NOT NULL," .
		"updated_dt TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW() NOT NULL" .
		") WITH (OIDS=FALSE)");
	logDoQuery($dbu,"CREATE SEQUENCE adm_config_confid_seq START WITH 1 INCREMENT BY 1 " .
		"NO MINVALUE NO MAXVALUE CACHE 1");
	logDoQuery($dbu,"ALTER SEQUENCE adm_config_confid_seq OWNED BY adm_config.confid");
	logDoQuery($dbu,"ALTER TABLE ONLY adm_config ALTER COLUMN confid " .
		"SET DEFAULT NEXTVAL('adm_config_confid_seq'::regclass)");
	logDoQuery($dbu,"ALTER TABLE ONLY adm_config ADD CONSTRAINT adm_config_pkey PRIMARY KEY (confid)");
}
?>