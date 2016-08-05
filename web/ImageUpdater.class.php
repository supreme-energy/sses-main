<?php
require_once("dbio.class.php");
require_once("AppInfo.class.php");

class ImageUpdater {

    function ImageUpdater($request) {
    	$this->app_info=new AppInfo($request);
    }
    function graphPDFWellboreMain(){}
    function graphWellboreMain(){}
    function graphWellboreSide(){}
    function graphWellboreBottom(){}
    function graphWellplan(){}
    function graphSGTAModeling(){}
    function graphPDFSnapshot(){}
}
?>