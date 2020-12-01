<?php 
function addDataJson($id, $with_data){
    global $db;
    $db->DoQuery('select * from edatalogs where id='.$id);
    $results = array();
    while($db->FetchRow()) {
        $id = $db->FetchField("id");
        $tablename = $db->FetchField("tablename");
        $db3=new dbio($seldbname);
        $db3->OpenDb();
        $db3->DoQuery('select count(*) as cnt from '.$tablename);
        $db3->FetchRow();
        $data_count = $db3->FetchField('cnt');
        $db3->CloseDb();
        $result = array(
            "id" => $id,
            "tablename" => $tablename,
            "label" => $db->FetchField("label"),
            "color" => $db->FetchField("color"),
            "scalelo" => $db->FetchField('scalelo'),
            "scalehi" => $db->FetchField('scalehi'),
            "logscale"  => $db->FetchField('logscale'),
            "enabled"    => $db->FetchField('enabled'),
            "color"  => $db->FetchField('color'),
            "single_plot" => $db->FetchField('single_plot'),
            "data_count"  => $data_count,
            "group_number" => $db->FetchField('group_number'),
            "bias" => $db->FetchField('bias'),
            "scale" =>  $db->FetchField('scale')
        );
        if($with_data){
            include("../read_edata_log.include.php");
            $result['data'] = $data;
        }
        array_push($results, $result);
    }
    return json_encode($results);
}
?>