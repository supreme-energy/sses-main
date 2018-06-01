<?php
if(!$dbu->ColumnExists('welllogs', 'isghost')){  
    logDoQuery($dbu, "alter table welllogs add isghost int not null default 0;");
}

if(!$dbu->ColumnExists('welllogs', 'raw_import_data')) {
	logDoQuery($dbu,"alter table welllogs add raw_import_data text default '';");
}    	
?>
