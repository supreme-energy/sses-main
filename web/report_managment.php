<?php
/*
 * Created on Jul 15, 2013
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("dbio.class.php");	
 $cmd = $_REQUEST['cmd'];
 $report_id =$_REQUEST['id'];
 $seldbname = $_REQUEST['seldbname'];
 $dbu=new dbio($seldbname);
 $dbu->OpenDb();
 switch($cmd){
 	case 'delete':
 		$query = "delete from reports where id = $report_id";
 		$dbu->DoQuery($query);
 		break;
 	case 'approve':
 		$query = "update reports set approved=1 where id = $report_id";
 		$dbu->DoQuery($query);
 		break;
 	default:
 		break;
 }
 header('Location: reports_tab.php?seldbname='.$seldbname);
?>
