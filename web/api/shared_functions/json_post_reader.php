<?php 
function jsonPostReadCreate($json, $allowed){
    $updates_array = array();
    $values_array  = array();
    $obj = json_decode($json);
    foreach($obj as $key => $value){
        if(in_array($key, $allowed)){
            $query_field_name = $key;
            if($key == 'sectdip' ){
                $query_field_name = 'dip';
            }
            
            $updates_array[$query_field_name] = "'$value'";
            $values_array[$query_field_name] =  "$query_field_name";
        }
    }
    return array($updates_array, $values_array);
}

function jsonPostReader($json, $allowed){
    $updates_array = array();
    $obj = json_decode($json);
    foreach($obj as $key => $value){
        if(in_array($key, $allowed)){
            $query_field_name = $key;
            if($key == 'sectdip' ){
                $query_field_name = 'dip';
            }
            $updates_array[$query_field_name] = "$query_field_name = '".$value."'";
        }
    }
    return array($updates_array, $obj->id);
}

function queryReader($request, $allowed){
    $updates_array = array();
    foreach ($allowed as $field_name) {
        if (isset($request[$field_name])) {
            $value = $request[$field_name];
            $query_field_name = $field_name;
            if ($field_name == 'unixtime_src') {
                $query_field_name = 'srcts';
            }
            if($field_name == 'sectdip' ){
                $query_field_name = 'dip';
            }
            array_push($updates_array, "$query_field_name = '$value'");
        }
    }
    return array($updates_array, $_REQUEST['id']);
}
?>