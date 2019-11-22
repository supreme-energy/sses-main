 <?php 
 include("../api_header.php");
 $id = $_REQUEST['id'];
 $query = "select * from surveys where plan=0 and id = " .$id;
 $db->DoQuery($query);
 $row = $db->FetchRow();
 if($row){
    $cur_md = $row['md'];
    $prev_query = "select * from surveys where md < $cur_md order by MD desc limit 1";
    $db->DoQuery($prev_query);
    $prev_row = $db->FetchRow();
    if($prev_row){                        
        $start_md = $prev_row['md'];
        $query = "delete from surveys where id = ". $id;
        $db->DoQuery($query);
        $query = "select * from welllogs where startmd > $start_md and endmd <= $cur_md";
        $db->DoQuery($query);
        $del_querys = "";
        $drop_querys = "";
        while($wlrow = $db->FetchRow()){
            $welllogid = $wlrow['id'];            
            $drop_querys .= "drop table ". $wlrow['tablename'].";";
            $del_querys .= "delete from welllogs where id = $welllogid;";
        }
        $db->DoQuery($del_querys);
        $db->DoQuery($drop_querys);
        $response = array("status"=>"success", "message"=>"survey deleted");
        exec("../../sses_gva -d $seldbname ");
        exec("../../sses_cc -d $seldbname");
        exec("../../sses_cc -d $seldbname -p");
        exec ("../../sses_af -d $seldbname");
    } else {
        $response = array("status"=>"failed", "message"=>"cannot delete tie in row");
    }
 } else {
    $response = array("status"=>"failed", "message"=>"no survey with id $id found");
 }
 echo json_encode($response);
 ?>