<?php
//  Written by: John P Arnold
//  Copyright: 2009, Digital Oil Tools
//  All rights reserved.
//  NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//  or distribute this file in any manner without written permission of Digital Oil Tools

// check that the database name was sent

if(!isset($_REQUEST['sdb']) or $_REQUEST['sdb'] == '')
{
	$error = array('res' => 'ERR','msg' => 'Did Not Define DB Name');
	echo json_encode($error);
	exit();
}
$sdb = $_REQUEST['sdb'];

// check that an action was sent

if(!isset($_REQUEST['a']))
{
	$error = array('res' => 'ERR','msg' => 'Did Not Define Action');
	echo json_encode($error);
        exit();
}

require_once("dbio.class.php");
$db = new dbio($sdb);
$db->OpenDb();

switch($_REQUEST['a'])
{

// check for set auto apply

case 'setauta':
	$sql = "update adm_config set cvalue = '" . trim($_REQUEST['s']) . "' where cname = 'autoapply'";
	if(($err = $db->DoQuery($sql)) != 0) $jsmg = array('res' => 'ERR','msg' => $db->stmt->errorInfo());
	else $jsmg = array('res' => 'OK','msg' => 'Cool');
	break;

// check for saving a configuration value

case 'setconf':
	$sql = "update adm_config set cvalue = '" . trim($_REQUEST['v']) . "' where cname = '" . trim($_REQUEST['n']) . "'";
	if(($err = $db->DoQuery($sql)) != 0) $jsmg = array('res' => 'ERR','msg' => $db->stmt->errorInfo());
	else $jsmg = array('res' => 'OK','msg' => 'Cool');
	break;

// check for set formations

case 'setform':
	$sql = "update addforms set {$_REQUEST['c']} = '{$_REQUEST['v']}' where id = {$_REQUEST['i']}";
	if(($err = $db->DoQuery($sql)) != 0) $jsmg = array('res' => 'ERR','msg' => $db->stmt->errorInfo());
	else $jsmg = array('res' => 'OK','msg' => 'Cool');
	break;

// if action not found do error

default:
	$jsmg = array('res' => 'ERR','msg' => 'Illegal Action');
}
echo json_encode($jsmg);
$db->CloseDb();
?>
