<?php
require_once("../dbio.class.php");
require_once('Config.class.php');
class AppInfo extends Config{
    function __construct($request) {
    	$this->query = "select * from appinfo limit 1;";
    	parent::__construct($request);
    }
}
?>