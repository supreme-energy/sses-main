<?php
require_once("../dbio.class.php");
require_once('Config.class.php');
class ControlLog extends Config{
    function __construct($request) {
    	$this->query = array("SELECT * FROM controllogs;");
    	parent::__construct($request);
    }
}
?>