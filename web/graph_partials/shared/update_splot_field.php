var sendSplotFieldUpdate = function(field,value){
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '/sses/json.php?path=json/sgta_modeling/update_splot_field.php&seldbname=<?php echo $seldbname ?>&field='+field+'&value='+value);
	xhr.send();
}

