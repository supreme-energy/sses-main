<?php
if(!$dbu->ColumnExists('surveys', 'srcts'))  
	logDoQuery($dbu, "alter table surveys add srcts double precision;");
if(!$dbu->ColumnExists('surveys', 'isghost'))  
	logDoQuery($dbu, "alter table surveys add isghost int not null default 0;");
if(!$dbu->ColumnExists('surveys', 'new'))  
	logDoQuery($dbu, "alter table surveys add \"new\" boolean default true;");
?>