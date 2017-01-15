<?php
/*
 * Created on Jan 14, 2017
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("dbio.class.php");
$seldbname=$_GET['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("select cvalue from adm_config where cname = 'msgtemplate' limit 1");
$currenttemplate = 'curved';
if($db->FetchRow()) $currenttemplate = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('msgtemplate','curved')");

if(isset($_GET['msgtemplate']) and $currenttemplate != $_GET['msgtemplate'])
{
	$db->DoQuery("update adm_config set cvalue = '{$_GET['msgtemplate']}' where cname = 'msgtemplate'");
	$currenttemplate = $_GET['msgtemplate'];
}

$thetemplate = $currenttemplate;

// getting the lateral 1 or 2

$db->DoQuery("select cvalue from adm_config where cname = 'lateralsel' limit 1");
$lateralsel = '1';
if($db->FetchRow()) $lateralsel = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('lateralsel','1')");

if(isset($_GET['lateralsel']) and $lateralsel != $_GET['lateralsel'])
{
 	$db->DoQuery("update adm_config set cvalue = '{$_GET['lateralsel']}' where cname = 'lateralsel'");
 	$lateralsel = $_GET['lateralsel'];
}

$db->DoQuery("select cvalue from adm_config where cname = 'curvedsel' limit 1");
$curvedsel = '1';
if($db->FetchRow()) $curvedsel = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('curvedsel','1')");

if(isset($_GET['curvedsel']) and $curvedsel != $_GET['curvedsel'])
{
 	$db->DoQuery("update adm_config set cvalue = '{$_GET['curvedsel']}' where cname = 'curvedsel'");
 	$curvedsel = $_GET['curvedsel'];
}
?>
