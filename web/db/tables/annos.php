<?php
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
?>