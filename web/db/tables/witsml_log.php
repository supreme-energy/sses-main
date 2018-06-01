<?php
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
?>
