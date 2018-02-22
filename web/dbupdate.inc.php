<?php
//	dbupdate.inc.php 
// 
//	Version: SSES v2.4.2 
//			 April 20, 2012 
// 
//	Modified by: C. Bergman  
//	Purpose: To add columns colortot, colotbot, and colorwp 
//			 to the wellinfo table, for storing the User
//           selected colors for the Top of Target (TOT), the
//           Bottom of Target (BOT), and the Well Plan Target
//           Line, respectively.  These columns are text fields
//           and are assigned the value 'ff0000' as default.
//			 The table wellinfo is then queried for the colors 
//			 when generating a graph from any tab in the SSES 
//			 Application. It was deemed best to store these
//           User-selected colors in the wellinfo table since
//           this table contains just one record per well, and
//		     therefore, this information could be stored in a
//			 unique location, thus promoting data integrity.  
//           The User-selected colors for the Top of Target
//           (TOT) and Bottom of Target (BOT) Lines may also be
//           stored in the addforms table if these formations
//           are defined for the database. For more information,
//           please see the Release Notes for Version 2.4.2. 
//
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools 	You may NOT modify, copy,
//	or distribute this file in any manner without written permission of
//	Digital Oil Tools

$response="";
function logInfo($s) {
	global $silent;
	global $response;
	if($silent==1) $response="$response$s\n";
	else echo $s;
}
function logDoQuery($dbp, $s) {
	logInfo($s);
	$dbp->DoQuery($s);
}

$dbu=new dbio("$seldbname");

$dbu->OpenDb();
$dbu->DoQuery("VACUUM ANALYZE;");

$gotinfo=0;
$gotlist=0;
$gotplist=0;
$gotproj=0;
$gotedatalogs=0;
$gotaddforms=0;
$gotaddformsdata=0;
$gotwitsml_details=0;
$gotwitsml_log=0;
$gorigminder_con=0;
$goadgfb=0;
$delsvyd = 0;
$delsvyg = 0;
$annot=0;
$reports=0;
$anticollision=0;
$admconfig=0;
$rotslide=0;
$ghostsurveys=0;
$ghostprojects = 0;
$ghostdata = 0;
$ghostwelllogs=0;
$nogo_zone=0;
$nogo_point=0;
$profile_lines = 0;
$import_config = 0;

$dbu->DoQuery("SHOW TABLES");
while($dbu->FetchRow()) {
	$tn=$dbu->FetchField("tablename");
	if($tn=="emailinfo")	$gotinfo=1;
	elseif($tn=="emaillist")	$gotlist=1;
	elseif($tn=="splotlist")	$gotplist=1;
	elseif($tn=="projections")	$gotproj=1;
	elseif($tn=="edatalogs")	$gotedatalogs=1;
	elseif($tn=="addforms")	$gotaddforms=1;
	elseif($tn=="addformsdata")	$gotaddformsdata=1;
	elseif($tn=="witsml_details") $gotwitsml_details=1;
	elseif($tn=="witsml_log") $gotwitsml_log=1;
	elseif($tn=="rigminder_connection") $gorigminder_con=1;
	elseif($tn=="add_data_gamma_fb") $goadgfb=1;
	elseif($tn=="deleted_survey_data") $delsvyd = 1;
	elseif($tn=="deleted_survey_group") $delsvyg=1;
	elseif($tn=='annos') $annot=1;
	elseif($tn=='reports') $reports=1;
	elseif($tn=="anticollision_wells") $anticollision=1;
	elseif($tn=="adm_config") $admconfig=1;
	elseif($tn=="rotslide") $rotslide=1;
	elseif($tn=="ghost_surveys") $ghostsurveys=1;
	elseif($tn=="ghost_projects") $ghostprojects=1;
	elseif($tn=="ghost_data") $ghostdata=1;
	elseif($tn=="nogo_zone") $nogo_zone=1;
	elseif($tn=="nogo_point") $nogo_point=1;
	elseif($tn=="profile_lines") $profile_lines=1;
	elseif($tn=="import_config") $import_config=1;
	
}
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

if($profile_lines==0){
	logDoQuery($dbu,"CREATE TABLE profile_lines (
    id integer NOT NULL,
    color text,
    reference_database TEXT DEFAULT '' NOT NULL,
    label TEXT DEFAULT '' NOT NULL,
    show_plot smallint DEFAULT 0 NOT NULL,
    show_report smallint DEFAULT 0 NOT NULL)");
	logDoQuery($dbu,"CREATE SEQUENCE profile_lines_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1");
	logDoQuery($dbu,"ALTER TABLE ONLY profile_lines ALTER COLUMN id SET DEFAULT nextval('profile_lines_id_seq'::regclass)");
	logDoQuery($dbu,"ALTER TABLE ONLY profile_lines
    ADD CONSTRAINT profile_lines_pkey PRIMARY KEY (id)");
}
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
// check if the rotate/slide table is present
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
if($rotslide == 0)
{
	logDoQuery($dbu,"create table rotslide (" .
		"rsid integer not null," .
		"rotstartmd numeric default 0.0 not null," .
		"rotendmd numeric default 0.0 not null," .
		"slidestartmd numeric default 0.0 not null," .
		"slideendmd numeric default 0.0 not null," .
		"tfo text default '' not null," .
		"rotstartvs numeric default 0.0 not null," .
		"rotendvs numeric default 0.0 not null," .
		"slidestartvs numeric default 0.0 not null," .
		"slideendvs numeric default 0.0 not null," .
		"updated_dt timestamp without time zone default now() not null" .
		") with (OIDS=FALSE)");
	logDoQuery($dbu,"CREATE SEQUENCE rotslide_rsid_seq START WITH 1 INCREMENT BY 1 " .
		"NO MINVALUE NO MAXVALUE CACHE 1");
	logDoQuery($dbu,"ALTER SEQUENCE rotslide_rsid_seq OWNED BY rotslide.rsid");
	logDoQuery($dbu,"ALTER TABLE ONLY rotslide ALTER COLUMN rsid " .
		"SET DEFAULT NEXTVAL('rotslide_rsid_seq'::regclass)");
	logDoQuery($dbu,"ALTER TABLE ONLY rotslide ADD CONSTRAINT rotslide_pkey PRIMARY KEY (rsid)");
}

// check if the configuration table is present

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

if($anticollision==0){
		logDoQuery($dbu,"create table anticollision_wells".
	"(" .
			"id serial not null," .
			"tablename text," .
			"realname text," .
			"color text," .			
			"propdir double precision not null default 0.0," .
			"eastingsl double precision not null default 0.0," .
			"northingsl double precision not null default 0.0," .
			"eastinglp double precision not null default 0.0," .
			"northinglp double precision not null default 0.0," .
			"eastingpbhl double precision not null default 0.0," .
			"northingpbhl double precision not null default 0.0," .
			"rkb double precision not null default 0.0," .
			"ground double precision not null default 0.0," .
			"correction text," .
			"coor_system text," .
			"constraint anticollision_wells_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");
}
if($reports==0){
	logDoQuery($dbu,"create table reports".
	"(" .
			"id serial not null," .
			"created timestamp not null default now()," .
			"report_type text not null," .
			"report_file text not null," .
			"approved int not null," .
			"constraint report_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");
}
if($annot==0){
	logDoQuery($dbu,"create table annos" .
			"(" .
			"id serial not null," .
			"created timestamp not null default now()," .
			"assigned_date timestamp not null," .
			"detail_assignments text not null," .
			"survey_id int not null," .
			"display_anno int not null default 1," .
			"constraint anno_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");	
}
if($delsvyg==0){
	logDoQuery($dbu,"create table deleted_survey_group" .
			" (" .
			"id serial not null," .
			"created timestamp not null default now(),".
			"constraint dsg_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");
}
if($delsvyd==0){
	logDoQuery($dbu,"create table deleted_survey_data" .
			" (" .
			"id serial not null," .
			"group_id int not null,".
			"azm float not null default 0.0," .
			"dl float not null default 0.0," .
			"ew float not null default 0.0," .
			"inc float not null default 0.0," .
			"md float not null default 0.0," .
			"ns float not null default 0.0," .
			"tvd float not null default 0.0," .
			"vs float not null default 0.0," .
			"ca float not null default 0.0," .
			"cd float not null default 0.0," .
			"cl float not null default 0.0," .
			"dip float not null default 0.0," .
			"fault float not null default 0.0," .
			"constraint dsd_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");
}
if($goadgfb==0){
	logDoQuery($dbu, "
	CREATE TABLE add_data_gamma_fb
	(
		id serial NOT NULL,
		md float NOT NULL DEFAULT 0.0,
		tvd float NOT NULL DEFAULT 0.0,
		vs float NOT NULL DEFAULT 0.0,
		value float NOT NULL DEFAULT 0.0,
		CONSTRAINT adgfb_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}	
if($gorigminder_con==0){
	logDoQuery($dbu, "
	CREATE TABLE rigminder_connection
	(
		id serial NOT NULL,
		host text NOT NULL DEFAULT '',
		username text NOT NULL DEFAULT '',
		password text NOT NULL DEFAULT '',
		dbname text NOT NULL DEFAULT '',
		data_transfer boolean NOT NULL DEFAULT false,
		CONSTRAINT rgc_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}
if($gotwitsml_details==0){
	logDoQuery($dbu, "
	CREATE TABLE witsml_details
	(
		id serial NOT NULL,
		endpoint text NOT NULL DEFAULT '',
		username text NOT NULL DEFAULT '',
		password text NOT NULL DEFAULT '',
		send_data boolean NOT NULL DEFAULT false,
		CONSTRAINT witsml_details_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}
if($gotwitsml_log==0){
	logDoQuery($dbu, "
	CREATE TABLE witsml_log
	(
		id serial NOT NULL,
		type text NOT NULL DEFAULT '',
		witsml text NOT NULL DEFAULT '',
		uid text NOT NULL DEFAULT '',
		sent_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		CONSTRAINT witsml_log_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");	
}
if($gotaddforms==0) {
	logDoQuery($dbu, "
	CREATE TABLE addforms
	(
		id serial NOT NULL,
		label text NOT NULL DEFAULT '',
		color text NOT NULL DEFAULT '0000ff',
		selected int NOT NULL DEFAULT 0,
		md float NOT NULL DEFAULT 0,
		tvd float NOT NULL DEFAULT 0,
		vs float NOT NULL DEFAULT 0,
		dip float NOT NULL DEFAULT 0,
		fault float NOT NULL DEFAULT 0,
		tot float NOT NULL DEFAULT 0,
		bot float NOT NULL DEFAULT 0,
		thickness float NOT NULL DEFAULT 0,
		CONSTRAINT addforms_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}
if($gotaddformsdata==1 && !$dbu->ColumnExists('addformsdata', 'thickness')) {
	logDoQuery($dbu, "DROP TABLE addformsdata;");
	$gotaddformsdata=0;
}
if($gotaddformsdata==0) {
	logDoQuery($dbu, "
	CREATE TABLE addformsdata
	(
		id serial NOT NULL,
		infoid int NOT NULL DEFAULT -1,
		svyid int NOT NULL DEFAULT -1,
		projid int NOT NULL DEFAULT -1,
		md float NOT NULL DEFAULT 0,
		tvd float NOT NULL DEFAULT 0,
		vs float NOT NULL DEFAULT 0,
		dip float NOT NULL DEFAULT 0,
		fault float NOT NULL DEFAULT 0,
		tot float NOT NULL DEFAULT 0,
		bot float NOT NULL DEFAULT 0,
		thickness float NOT NULL DEFAULT 0,
		CONSTRAINT addformsdata_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}

if($gotedatalogs==0) {
	logDoQuery($dbu, "
	CREATE TABLE edatalogs
	(
		id serial NOT NULL,
		colnum int NOT NULL DEFAULT 0,
		tablename text NOT NULL DEFAULT '',
		label text NOT NULL DEFAULT 'edata',
		scalelo float NOT NULL DEFAULT 0.0,
		scalehi float NOT NULL DEFAULT 300.0,
		enabled integer NOT NULL DEFAULT 0,
		logscale integer NOT NULL DEFAULT 0,
		color text NOT NULL DEFAULT '#0000ff',
		CONSTRAINT edatalogs_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}
if($gotinfo==0) {
	logDoQuery($dbu, "
	CREATE TABLE emailinfo
	(
		id serial NOT NULL,
		smtp_from text NOT NULL DEFAULT '',
		smtp_server text NOT NULL DEFAULT '',
		smtp_login text NOT NULL DEFAULT '',
		smtp_password text NOT NULL DEFAULT '',
		enabled integer NOT NULL DEFAULT 1,
		CONSTRAINT emailinfo_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
	logDoQuery($dbu, "INSERT INTO emailinfo (smtp_from) VALUES ('email@yourdomain.com');");
}
if($gotlist==0) {
	logDoQuery($dbu, "
	CREATE TABLE emaillist
	(
		id serial NOT NULL,
		name text NOT NULL DEFAULT '',
		email text NOT NULL DEFAULT '',
		enabled integer NOT NULL DEFAULT 1,
		CONSTRAINT emaillist_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}
if($gotplist==0) {
	logDoQuery($dbu, "
	CREATE TABLE splotlist
	(
		id serial NOT NULL,
		ptype text NOT NULL DEFAULT '',
		mtype text NOT NULL DEFAULT '',
		inputa float NOT NULL DEFAULT 5.0,
		inputb float NOT NULL DEFAULT 6.0,
		CONSTRAINT splotlist_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}
if($gotproj==0) {
	logDoQuery($dbu, "
	CREATE TABLE projections
	(
		id serial NOT NULL,
		hide int NOT NULL DEFAULT 0,
		method int NOT NULL DEFAULT 0,
		data text NOT NULL DEFAULT '0,0,0',
		md float NOT NULL DEFAULT 0.0,
		inc float NOT NULL DEFAULT 0.0,
		azm float NOT NULL DEFAULT 0.0,
		tvd float NOT NULL DEFAULT 0.0,
		vs float NOT NULL DEFAULT 0.0,
		ns float NOT NULL DEFAULT 0.0,
		ew float NOT NULL DEFAULT 0.0,
		ca float NOT NULL DEFAULT 0.0,
		cd float NOT NULL DEFAULT 0.0,
		cl float NOT NULL DEFAULT 0.0,
		dl float NOT NULL DEFAULT 0.0,
		tot float NOT NULL DEFAULT 0.0,
		bot float NOT NULL DEFAULT 0.0,
		dip float NOT NULL DEFAULT 0.0,
		fault float NOT NULL DEFAULT 0.0,
		CONSTRAINT projections_pkey PRIMARY KEY (id)
	)
	WITH ( OIDS=FALSE);");
}

if(!$dbu->ColumnExists('splotlist', 'mintvd'))
	logDoQuery($dbu, "alter table splotlist add mintvd float not null default 99999.0;");
if(!$dbu->ColumnExists('splotlist', 'maxtvd'))
	logDoQuery($dbu, "alter table splotlist add maxtvd float not null default -99999.0;");
if(!$dbu->ColumnExists('splotlist', 'minvs'))
	logDoQuery($dbu, "alter table splotlist add minvs float not null default 99999.0;");
if(!$dbu->ColumnExists('splotlist', 'maxvs'))
	logDoQuery($dbu, "alter table splotlist add maxvs float not null default -99999.0;");

if(!$dbu->ColumnExists('emaillist', 'phone'))
	logDoQuery($dbu, "alter table emaillist add phone text not null default '';");
if(!$dbu->ColumnExists('emaillist', 'cat'))
	logDoQuery($dbu, "alter table emaillist add cat text not null default 'Operator';");

if(!$dbu->ColumnExists('emailinfo', 'smtp_message'))
	logDoQuery($dbu, "alter table emailinfo add smtp_message text not null default 'Email report';");

if(!$dbu->ColumnExists('appinfo', 'showxy'))
	logDoQuery($dbu, "alter table appinfo add showxy int not null default 0;");
if(!$dbu->ColumnExists('appinfo', 'sgtastart'))
	logDoQuery($dbu, "alter table appinfo add sgtastart float not null default 0.0;");
if(!$dbu->ColumnExists('appinfo', 'sgtaend'))
	logDoQuery($dbu, "alter table appinfo add sgtaend float not null default 100.0;");
if(!$dbu->ColumnExists('appinfo', 'sgtacutin'))
	logDoQuery($dbu, "alter table appinfo add sgtacutin float not null default 0.0;");
if(!$dbu->ColumnExists('appinfo', 'sgtacutoff'))
	logDoQuery($dbu, "alter table appinfo add sgtacutoff float not null default 99999.0;");
if(!$dbu->ColumnExists('appinfo', 'dataset'))
	logDoQuery($dbu, "alter table appinfo add dataset integer not null default 1;");
if(!$dbu->ColumnExists('appinfo', 'tablename'))
	logDoQuery($dbu, "alter table appinfo add tablename text not null default '';");
if(!$dbu->ColumnExists('appinfo', 'lastptype'))
	logDoQuery($dbu, "alter table appinfo add lastptype text not null default 'LAT';");
if(!$dbu->ColumnExists('appinfo', 'lastmtype'))
	logDoQuery($dbu, "alter table appinfo add lastmtype text not null default 'INC';");
if(!$dbu->ColumnExists('appinfo', 'uselogscale'))
	logDoQuery($dbu, "alter table appinfo add uselogscale int not null default 0;");
if(!$dbu->ColumnExists('appinfo', 'viewdspcnt'))
	logDoQuery($dbu, "alter table appinfo add viewdspcnt int not null default 0;");
if(!$dbu->ColumnExists('appinfo', 'dataavg'))
	logDoQuery($dbu, "alter table appinfo add dataavg int not null default 0;");
if(!$dbu->ColumnExists('appinfo','dmod'))
	logDoQuery($dbu,"alter table appinfo add dmod int not null default 10;");
// cached datasets
if(!$dbu->ColumnExists('appinfo', 'dscache_dip'))
	logDoQuery($dbu, "alter table appinfo add dscache_dip float not null default 0.0;");
if(!$dbu->ColumnExists('appinfo', 'dscache_fault'))
	logDoQuery($dbu, "alter table appinfo add dscache_fault float not null default 0.0;");
if(!$dbu->ColumnExists('appinfo', 'dscache_bias'))
	logDoQuery($dbu, "alter table appinfo add dscache_bias float not null default 0.0;");
if(!$dbu->ColumnExists('appinfo', 'dscache_scale'))
	logDoQuery($dbu, "alter table appinfo add dscache_scale float not null default 1.0;");
if(!$dbu->ColumnExists('appinfo', 'dscache_freeze'))
	logDoQuery($dbu, "alter table appinfo add dscache_freeze int not null default 0;");
if(!$dbu->ColumnExists('appinfo', 'dscache_md'))
	logDoQuery($dbu, "alter table appinfo add dscache_md float not null default 99999.0;");
if(!$dbu->ColumnExists('appinfo', 'dscache_plotstart'))
	logDoQuery($dbu, "alter table appinfo add dscache_plotstart float not null default 0.0;");
if(!$dbu->ColumnExists('appinfo', 'dscache_plotend'))
	logDoQuery($dbu, "alter table appinfo add dscache_plotend float not null default 99999.0;");
if(!$dbu->ColumnExists('appinfo', 'sgta_off'))
	logDoQuery($dbu, "alter table appinfo add sgta_off int not null default 0;");
if(!$dbu->ColumnExists('appinfo', 'anno_settings'))
	logDoQuery($dbu, "alter table appinfo add anno_settings text not null default '';");

if(!$dbu->ColumnExists('wellinfo', 'bitoffset'))
	logDoQuery($dbu, "alter table wellinfo add bitoffset float not null default 0.0;");
if(!$dbu->ColumnExists('wellinfo', 'projdip'))
	logDoQuery($dbu, "alter table wellinfo add projdip float not null default 0.0;");
if(!$dbu->ColumnExists('wellinfo', 'pbhl_easting'))
	logDoQuery($dbu, "alter table wellinfo add pbhl_easting float not null default 0.0;");
if(!$dbu->ColumnExists('wellinfo', 'pbhl_northing'))
	logDoQuery($dbu, "alter table wellinfo add pbhl_northing float not null default 0.0;");
if(!$dbu->ColumnExists('wellinfo', 'survey_easting'))
	logDoQuery($dbu, "alter table wellinfo add survey_easting float not null default 0.0;");
if(!$dbu->ColumnExists('wellinfo', 'survey_northing'))
	logDoQuery($dbu, "alter table wellinfo add survey_northing float not null default 0.0;");
if(!$dbu->ColumnExists('wellinfo', 'landing_easting'))
	logDoQuery($dbu, "alter table wellinfo add landing_easting float not null default 0.0;");
if(!$dbu->ColumnExists('wellinfo', 'landing_northing'))
	logDoQuery($dbu, "alter table wellinfo add landing_northing float not null default 0.0;");
if(!$dbu->ColumnExists('wellinfo', 'elev_ground'))
	logDoQuery($dbu, "alter table wellinfo add elev_ground float not null default 0.0;");
if(!$dbu->ColumnExists('wellinfo', 'elev_rkb'))
	logDoQuery($dbu, "alter table wellinfo add elev_rkb float not null default 0.0;");
if(!$dbu->ColumnExists('wellinfo', 'correction'))
	logDoQuery($dbu, "alter table wellinfo add correction text not null default 'True North';");
if(!$dbu->ColumnExists('wellinfo', 'coordsys'))
	logDoQuery($dbu, "alter table wellinfo add coordsys text not null default 'Polar';");
if(!$dbu->ColumnExists('wellinfo', 'startdate'))
	logDoQuery($dbu, "alter table wellinfo add startdate date;");
if(!$dbu->ColumnExists('wellinfo', 'enddate'))
	logDoQuery($dbu, "alter table wellinfo add enddate date;");
if(!$dbu->ColumnExists('wellinfo', 'padata'))
	logDoQuery($dbu, "alter table wellinfo add padata text not null default '0,0,0';");
if(!$dbu->ColumnExists('wellinfo', 'pbdata'))
	logDoQuery($dbu, "alter table wellinfo add pbdata text not null default '0,0,0';");
if(!$dbu->ColumnExists('wellinfo', 'pamethod'))
	logDoQuery($dbu, "alter table wellinfo add pamethod int not null default 0;");
if(!$dbu->ColumnExists('wellinfo', 'autoposdec'))
	logDoQuery($dbu, "alter table wellinfo add autoposdec int not null default 0;");
if(!$dbu->ColumnExists('wellinfo', 'pbmethod'))
	logDoQuery($dbu, "alter table wellinfo add pbmethod int not null default 0;");
if(!$dbu->ColumnExists('wellinfo', 'colortot'))  
	logDoQuery($dbu, "alter table wellinfo add colortot text not null  default 'ff0000';");  

if(!$dbu->ColumnExists('wellinfo', 'colorbot'))  
	logDoQuery($dbu, "alter table wellinfo add colorbot text not null  default 'ff0000';");  

if(!$dbu->ColumnExists('wellinfo', 'colorwp'))  
	logDoQuery($dbu, "alter table wellinfo add colorwp text not null  default 'ff0000';");

if(!$dbu->ColumnExists('wellinfo', 'motoryield'))  
	logDoQuery($dbu, "alter table wellinfo add motoryield float;");

if(!$dbu->ColumnExists('wellinfo', 'pterm_method'))  
	logDoQuery($dbu, "alter table wellinfo add pterm_method text not null default 'bc';");    

if(!$dbu->ColumnExists('appinfo', 'dsholdfault'))  
	logDoQuery($dbu, "alter table appinfo add dsholdfault float not null  default 0;");

if(!$dbu->ColumnExists('projections', 'tf'))  
	logDoQuery($dbu, "alter table projections add tf text;");

if(!$dbu->ColumnExists('projections', 'ptype'))  
	logDoQuery($dbu, "alter table projections add ptype text default 'pa';");

if(!$dbu->ColumnExists('surveys', 'srcts'))  
	logDoQuery($dbu, "alter table surveys add srcts double precision;");
if(!$dbu->ColumnExists('surveys', 'isghost'))  
	logDoQuery($dbu, "alter table surveys add isghost int not null default 0;");
if(!$dbu->ColumnExists('welllogs', 'isghost'))  
	logDoQuery($dbu, "alter table welllogs add isghost int not null default 0;");	
if(!$dbu->ColumnExists('witsml_details', 'wellid'))  
	logDoQuery($dbu, "alter table witsml_details add wellid text;");

if(!$dbu->ColumnExists('witsml_details', 'boreid'))  
	logDoQuery($dbu, "alter table witsml_details add boreid text;");

if(!$dbu->ColumnExists('witsml_details', 'trajid'))  
	logDoQuery($dbu, "alter table witsml_details add trajid text;");

if(!$dbu->ColumnExists('rigminder_connection', 'aisd'))  
	logDoQuery($dbu, "alter table rigminder_connection add aisd float default 0.0;");

if(!$dbu->ColumnExists('surveys', 'new'))  
	logDoQuery($dbu, "alter table surveys add \"new\" boolean default true;");

if(!$dbu->ColumnExists('wellinfo', 'sgta_show_formations'))  
	logDoQuery($dbu, "alter table wellinfo add column sgta_show_formations int default 0;");

if(!$dbu->ColumnExists('wellinfo', 'wb_show_formations'))  
	logDoQuery($dbu, "alter table wellinfo add column wb_show_formations int default 1;");    

if(!$dbu->ColumnExists('wellinfo', 'vsland')) {
	logDoQuery($dbu, "alter table wellinfo add vsland float not null default 0.0;");
}
if(!$dbu->ColumnExists('wellinfo', 'vsldip')) {
	logDoQuery($dbu, "alter table wellinfo add vsldip float not null default 0.0;");
}
if(!$dbu->ColumnExists('wellinfo', 'vslon')) {
	logDoQuery($dbu,"alter table wellinfo add vslon int not null default 0;");
}
if(!$dbu->ColumnExists("wellinfo","refwellname")){
	logDoQuery($dbu,"alter table wellinfo add refwellname text;");
}
if(!$dbu->ColumnExists("rigminder_connection","connection_type")){
	logDoQuery($dbu,"alter table rigminder_connection add connection_type text;");
}
if(!$dbu->ColumnExists("witsml_details","logid")){
	logDoQuery($dbu,"alter table witsml_details add logid text;");
}
if(!$dbu->ColumnExists("controllogs","azm")){
	logDoQuery($dbu,"alter table controllogs add azm float not null default 0.0;");
}
if(!$dbu->ColumnExists('wellinfo', 'autodipconfig')) {
	logDoQuery($dbu,"alter table wellinfo add autodipconfig text;");
}
if(!$dbu->ColumnExists('wellinfo', 'xaxis')) {
	logDoQuery($dbu,"alter table wellinfo add xaxis int default 90;");
}
if(!$dbu->ColumnExists('wellinfo', 'zaxis')) {
	logDoQuery($dbu,"alter table wellinfo add zaxis int default 180;");
}
if(!$dbu->ColumnExists('wellinfo', 'zoom3d')) {
	logDoQuery($dbu,"alter table wellinfo add zoom3d int default 75;");
}
if(!$dbu->ColumnExists('wellinfo', 'originh3d')) {
	logDoQuery($dbu,"alter table wellinfo add originh3d float default 0.0;");
}
if(!$dbu->ColumnExists('wellinfo', 'originv3d')) {
	logDoQuery($dbu,"alter table wellinfo add originv3d float default 0.0;");
}
if(!$dbu->ColumnExists('wellinfo', 'rt_stream_status')) {
	logDoQuery($dbu,"alter table wellinfo add rt_stream_status int default 0;");
}
if(!$dbu->ColumnExists('wellinfo', 'rt_stream_ghost')) {
	logDoQuery($dbu,"alter table wellinfo add rt_stream_ghost int default 0;");
}
if(!$dbu->ColumnExists('wellinfo', 'rt_stream_ld')) {
	logDoQuery($dbu,"alter table wellinfo add rt_stream_ld float default 0.0;");
}
if(!$dbu->ColumnExists('wellinfo', 'rt_stream_test')) {
	logDoQuery($dbu,"alter table wellinfo add rt_stream_test int default 0;");
}

if(!$dbu->ColumnExists('appinfo', 'labeling_start')) {
	logDoQuery($dbu,"alter table appinfo add labeling_start float default 0.0;");
}

if(!$dbu->ColumnExists('appinfo', 'label_every')) {
	logDoQuery($dbu,"alter table appinfo add label_every int default 0;");
}

if(!$dbu->ColumnExists('appinfo', 'label_dmd')) {
	logDoQuery($dbu,"alter table appinfo add label_dmd int default 0;");
}

if(!$dbu->ColumnExists('appinfo', 'label_dvs')) {
	logDoQuery($dbu,"alter table appinfo add label_dvs int default 0;");
}

if(!$dbu->ColumnExists('appinfo', 'label_dreport')) {
	logDoQuery($dbu,"alter table appinfo add label_dreport int default 0;");
}

if(!$dbu->ColumnExists('appinfo', 'label_orient')) {
	logDoQuery($dbu,"alter table appinfo add label_orient int default 0;");
}

if(!$dbu->ColumnExists('appinfo', 'label_dwebplot')) {
	logDoQuery($dbu,"alter table appinfo add label_dwebplot int default 0;");
}

if(!$dbu->ColumnExists('appinfo', 'auto_gr_mnemonic')) {
	logDoQuery($dbu,"alter table appinfo add auto_gr_mnemonic varchar(255) default 'GR';");
}

if(!$dbu->ColumnExists('appinfo', 'import_alarm_enabled')) {
	logDoQuery($dbu,"alter table appinfo add import_alarm_enabled int default 0;");
}
if(!$dbu->ColumnExists('appinfo', 'ghost_dip')) {
	logDoQuery($dbu,"alter table appinfo add ghost_dip double precision DEFAULT 0 NOT NULL;");
}
if(!$dbu->ColumnExists('appinfo', 'ghost_fault')) {
	logDoQuery($dbu,"alter table appinfo add ghost_fault double precision DEFAULT 0 NOT NULL;");
}
if(!$dbu->ColumnExists('appinfo', 'import_alarm')) {
	logDoQuery($dbu,"alter table appinfo add import_alarm varchar(255) default '';");
}

if(!$dbu->ColumnExists('appinfo', 'email_attach_las')) {
	logDoQuery($dbu,"alter table appinfo add email_attach_las int default 0;");
}

if(!$dbu->ColumnExists('appinfo', 'email_attach_r1')) {
	logDoQuery($dbu,"alter table appinfo add email_attach_r1 int default 0;");
}

if(!$dbu->ColumnExists('appinfo', 'email_attach_r2')) {
	logDoQuery($dbu,"alter table appinfo add email_attach_r2 int default 0;");
}

if(!$dbu->ColumnExists('emaillist', 'las_file')) {
	logDoQuery($dbu,"alter table emaillist add las_file int default 0;");
}

if(!$dbu->ColumnExists('emaillist', 'report_1')) {
	logDoQuery($dbu,"alter table emaillist add report_1 int default 0;");
}

if(!$dbu->ColumnExists('emaillist', 'report_2')) {
	logDoQuery($dbu,"alter table emaillist add report_2 int default 0;");
}

if(!$dbu->ColumnExists('welllogs', 'raw_import_data')) {
	logDoQuery($dbu,"alter table welllogs add raw_import_data text default '';");
}

if(!$dbu->ColumnExists('rotslide', 'md')) {
	logDoQuery($dbu,"alter table rotslide add md varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('rotslide', 'bur')) {
	logDoQuery($dbu,"alter table rotslide add bur varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('rotslide', 'turn_rate')) {
	logDoQuery($dbu,"alter table rotslide add turn_rate varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('rotslide', 'motor_yield')) {
	logDoQuery($dbu,"alter table rotslide add motor_yield varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('addforms', 'bg_color')) {
	logDoQuery($dbu,"alter table addforms add bg_color varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('addforms', 'bg_percent')) {
	logDoQuery($dbu,"alter table addforms add bg_percent double precision not null default 0.0");
}

if(!$dbu->ColumnExists('addforms', 'pat_color')) {
	logDoQuery($dbu,"alter table addforms add pat_color varchar(32) not null default ''");
}

if(!$dbu->ColumnExists('addforms', 'pat_num')) {
	logDoQuery($dbu,"alter table addforms add pat_num int not null default 0");
}

if(!$dbu->ColumnExists('addforms', 'show_line')) {
	logDoQuery($dbu,"alter table addforms add show_line varchar(8) not null default 'Yes'");
}

if(!$dbu->ColumnExists('edatalogs', 'single_plot')) {
	logDoQuery($dbu,"alter table edatalogs add single_plot int not null default 0");
}

if(!$dbu->ColumnExists('profile_lines', 'pattern')) {
	logDoQuery($dbu,"alter table profile_lines add pattern text default '0'");
}


// logInfo("dbupdate: Finished");
$dbu->CloseDb();

if(strlen($response)>0) { ?>
	<BODY style='background-color: rgb(255, 255, 252);'>
	<H2>Database has been updated with the following changes:</H2>
	<?echo "<pre>$response</pre>"; ?>
	<A href='gva_tab1.php?seldbname=<?echo $seldbname;?>'>Click To Continue</A>
	</BODY>
<?
	exit;
} ?>
