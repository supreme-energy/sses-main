<?php


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
if(!$dbu->ColumnExists('wellinfo', 'sgta_show_formations')){  
	logDoQuery($dbu, "alter table wellinfo add column sgta_show_formations int default 0;");
}

if(!$dbu->ColumnExists('wellinfo', 'wb_show_formations')){
	logDoQuery($dbu, "alter table wellinfo add column wb_show_formations int default 1;");    
}
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
?>