<?php
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
?>