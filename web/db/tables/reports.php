<?php
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
?>