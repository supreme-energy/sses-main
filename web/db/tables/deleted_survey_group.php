<?php
if($delsvyg==0){
	logDoQuery($dbu,"create table deleted_survey_group" .
			" (" .
			"id serial not null," .
			"created timestamp not null default now(),".
			"constraint dsg_pkey PRIMARY KEY(id)" .
			") WITH (OIDS=FALSE)");
}
?>