<?php
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
?>