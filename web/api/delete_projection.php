 <?php 
 include("api_header.php");
 $id = $_REQUEST['id'];
 $query = "delete from projections where id = ". $id;
 $db->DoQuery($query);
 $response = array("status"=>"success", "message"=>"projection deleted");
 exec("../sses_gva -d $seldbname ");
 exec("../sses_cc -d $seldbname");
 exec("../sses_cc -d $seldbname -p");
 exec ("../sses_af -d $seldbname");
 echo json_encode($response);
 ?>