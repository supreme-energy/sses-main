<?php
class SSESClass
{
public $mod_name;

public static $page_title = 'SSES, Inc';
public static $errmsg;
public static $infomsg;
public static $parms; // input parameters
public static $action; // action name
public static $config;
public static $login;
public static $empid;
public static $coid; // company ID
public static $company_name;
public static $full_name;
public static $force_menu;
public static $default_menu;
public static $groups;
public static $db;

function __construct($name)
{
	$this->mod_name = $name;
}

static function get_req_parms()
{
	switch($_SERVER['REQUEST_METHOD'])
	{
		case 'GET': self::$parms = &$_GET; break;
		case 'POST': self::$parms = &$_POST; break;
		default: self::$parms = array();
	}
	if(isset(self::$parms['s']) and is_array(self::$parms['s']))
	{
		foreach(self::$parms['s'] as $key => $val)
		{
			if(is_array($val)) continue;
			self::$parms['s'][$key] = trim($val);
		}
	}
}

// -------------------------------------------
// show message in a paragraph

function show($str)
{
	echo "<p class='comment'>({$this->mod_name}) $str</p>\n";
	return true;
}

// -------------------------------------------
// show error message and return false

function show_error($str)
{
	echo "<p class='error'>({$this->mod_name}) $str</p>";
	return false;
}

// -------------------------------------------
// page start

function page_start()
{
?>
<!DOCTYPE HTML>
<html>

<head>

<title><?php echo self::$page_title ?></title>

<link rel='stylesheet' type='text/css' href='sses-style.css?v=1.1'>

<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js'></script>
<script src='sses-lib.js'></script>

</head>

<body>

<div class='sses_base'>

<?php
	return true;
}

// -------------------------------------------
// page end

function page_end()
{
?>

</div> <!-- end of sses_base -->

<form action='<?php echo SSES_HOME_PAGE ?>' method='post' id='sses-action-call'>
    <input id='sses-action-call-a' type='hidden' name='a' value='' />
</form>

<!-- start of fade & overlay div -->

<div id='sses-fade'></div>

<div id='sses-overlay'>
    <div style='border:1px solid black;min-height:200px'>
		<div id='sses-overlay-top'>
			<div id='sses-overlay-top-title'>SSES, Inc</div>
			<div id='sses-overlay-top-close' onclick='ssesCloseOverlay()'>Close</div>
			<div style='clear:both'></div>
		</div>
		<div id='sses-overlay-body'></div>
	</div>
</div> <!-- end of fade & overlay div -->

<?php
if(self::$errmsg != '')
{
?>
<script>
$(document).ready(function() {
    var str = "<?php echo addcslashes(str_replace("\r\n","",self::$errmsg),"\"\n") ?>";
    ssesOpenOverlay(str,"<span style='color:red;font-weight:bolder;background-color:white;padding:0 10px'>ERROR MESSAGE</span>");
});
</script>

</body>

</html>
<?php
}

elseif(self::$infomsg != '')
{
?>
<script>
$(document).ready(function() {
    var str = "<?php echo addcslashes(str_replace("\r\n","",self::$infomsg),"\"'\n") ?>";
    ssesOpenOverlay(str,"INFORMATION MESSAGE");
});
</script>

<?php
}
	return true;
}

// -------------------------------------------
// connect to database

static function connect($hostname,$username,$password,$database,$port='')
{
	$connstr = "host=$hostname dbname=$database user=$username password=$password";
	if($port != '') $connstr .= " port=$port";

	if((self::$db = pg_connect($connstr)) === false)
	{
		echo "<p class='error'>PostgreSQL Error: Unable to connect to $hostname as $username</p>\n";
		return false;
	}
	return true;
}

// -------------------------------------------
// get array of rows from query

function get_data_array($sql,&$data,$fetch_type = 'object')
{
	if(($res = pg_query($sql)) === false)
	{
		self::$errmsg = "({$this->mod_name}) " . pg_last_error();
		return false;
	}
	if(pg_num_rows($res) > 0)
	{
		switch($fetch_type)
		{
		case 'object':
			while($row = pg_fetch_object($res)) $data[] = $row;
			break;
		case 'array':
			while($row = pg_fetch_row($res)) $data[] = $row;
			break;
		case 'assoc':
			while($row = pg_fetch_assoc($res)) $data[] = $row;
			break;
		}
		pg_free_result($res);
	}
	return true;
}

// -------------------------------------------
// get single record

function get_data($sql,$fetch_type = 'object',$show_no_data_err = false)
{
	if(($res = pg_query($sql)) === false)
	{
		self::$errmsg = "({$this->mod_name}) " . pg_last_error();
		return false;
	}
	if(pg_num_rows($res) < 1)
	{
		if($show_no_data_err == true) self::$errmsg = "({$this->mod_name}) Did Not Find Data Requested";
		return false;
	}
	switch($fetch_type)
	{
		case 'object':
			$row = pg_fetch_object($res);
			break;
		case 'array':
			$row = pg_fetch_row($res);
			break;
		case 'assoc':
			$row = pg_fetch_assoc($res);
			break;
		default:
			self::$errmsg = 'Defined Illegal Object Type';
			return false;
	}
	pg_free_result($res);
	return $row;
}

// -------------------------------------------
// get single record object

function get_object($sql,$show_no_data_err = false)
{
	return $this->get_data($sql,'object',$show_no_data_err);
}

// -------------------------------------------
// get a single value

function get_val($sql,$show_no_data_err = false)
{
	if(($res = pg_query($sql)) === false)
	{
		self::$errmsg = "({$this->mod_name}) " . pg_last_error();
		return false;
	}
	if(pg_num_rows($res) < 1)
	{
		if($show_no_data_err == true) self::$errmsg = "({$this->mod_name}) Did Not Find Data Requested";
		return false;
	}
	$row = pg_fetch_row($res);
	pg_free_result($res);
	return $row[0];
}

// -------------------------------------------
// close the database

function close()
{
	pg_close();
}
}
?>
