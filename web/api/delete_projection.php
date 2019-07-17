 <?php 
 include("api_header.php");
 $id = $_REQUEST['id'];
 $query = "delete from projections where id = ". $id;
 $db->DoQuery($query);
 $response = array("status"=>"success", "message"=>"projection deleted");
 echo json_encode($response);
 ?>