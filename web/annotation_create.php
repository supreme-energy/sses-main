<?php
/*
 * Created on Apr 5, 2013
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("dbio.class.php");
 require_once("classes/Annotation.class.php");
 $annotation_loader = new Annotation($_REQUEST);
 
 $sid = $_REQUEST['annotation_survey'];
 $settime = date('Y-m-d h:i a',strtotime($_REQUEST['anno_date'].' '.$_REQUEST['anno_time']));
 $settings=$_REQUEST['comment'];
 $annotation_loader->create($sid,$settime,$settings);
 header('Location: annotations.php?seldbname='.$_REQUEST['seldbname']);

?>