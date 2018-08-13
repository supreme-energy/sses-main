var sendAddformUpdate = function(id,label,thickness){
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '/sses/json.php?path=json/sgta_modeling/update_addform.php&seldbname=<?php echo $seldbname ?>&id='+id+'&label='+label+'&thickness='+thickness);
	xhr.send();
}
