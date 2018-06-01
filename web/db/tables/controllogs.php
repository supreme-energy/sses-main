<?php
if(!$dbu->ColumnExists("controllogs","azm")){
	logDoQuery($dbu,"alter table controllogs add azm float not null default 0.0;");
}
?>