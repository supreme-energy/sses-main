var sendAppInfoFieldUpdate = function(field,value){
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '/sses/json.php?path=json/sgta_modeling/update_wellinfo_field.php&seldbname=<?php echo $seldbname ?>&field='+field+'&value='+value);
	xhr.send();
}