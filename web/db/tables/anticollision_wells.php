<?php
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
?>