<?php
$program = $_REQUEST['program'];
include_once($program);
echo "<script>window.close()</script>";
?>