<?php
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
if(!$dbu->ColumnExists('appinfo', 'dsholdfault'))  
	logDoQuery($dbu, "alter table appinfo add dsholdfault float not null  default 0;");    
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
?>