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
 $aid = $_REQUEST['aid'];
 $annotation_loader->delete($aid);
 header('Location: annotations.php?seldbname='.$_REQUEST['seldbname']);

?>