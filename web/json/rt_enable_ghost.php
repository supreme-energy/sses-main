<?php
/*
 * Created on Jan 16, 2016
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
  require_once("../dbio.class.php");
$call_back = $_REQUEST['callback'];
header('Content-type: application/json');
$db_name = $_REQUEST['seldbname'];

$sql ="update wellinfo set rt_stream_ghost=1 ";
$db=new dbio("$db_name"); 
$db->OpenDb();
$db->DoQuery($sql);
$sql_pa_ks = "select * from surveys order by id desc limit 2;";
$db->DoQuery($sql_pa_ks);
$pa=$db->FetchRow();
$ks=$db->FetchRow();
$sql ="select ghost_dip,ghost_fault from appinfo limit 1;";
$db->DoQuery($sql);
$ghost_df = $db->FetchRow();
//print_r($ghost_df);
$dip = $ghost_df["ghost_dip"];
$fault = $ghost_df["ghost_fault"];
$sql = "select * from ghost_data order by id desc limit 1;";
$db->DoQuery($sql);
$gma = $db->FetchRow();
$sql = "select * from ghost_data order by id asc limit 1;";
$db->DoQuery($sql);
$gsp = $db->FetchRow();
$sql = "select * from welllogs order by id desc limit 1;";
$db->DoQuery($sql);
$welllog_l = $db->FetchRow();


$sbias = $welllog_l['scalebias'];
$sfactor =  $welllog_l['scalefactor'];
$vsks = $ks['vs'];
$mdks = $ks['md'];
$incks = $ks['inc'];
$azmks=$ks['azm'];
$nsks=$ks['ns'];
$ewks=$ks['ew'];
$cdks=$ks['cd'];
$caks=$ks['ca'];

$mdpa = $pa['md'];
$incpa = $pa['inc'];
$azmpa = $pa['azm'];
$nspa=$pa['ns'];
$ewpa=$pa['ew'];
$cdpa=$pa['cd'];
$capa=$pa['ca'];
			
				
$md=$gma['md'];
$tvd = $gma['tvd'];
$vs  = $gma['vs'];
$depth = $gma['depth'];
$svs = $gsp['vs'];
$stvd = $gsp['tvd'];
$smd = $gsp['md']+1;
$sdepth = $gsp['depth'];
$azm=(($md-$mdks)*($azmpa-$azmks)/($mdpa-$mdks))+$azmks;
$inc=(($md-$mdks)*($incpa-$incks)/($mdpa-$mdks))+$incks;
$ew=(($md-$mdks)*($nspa-$nsks)/($mdpa-$mdks))+$nsks;
$ns=(($md-$mdks)*($ewpa-$ewks)/($mdpa-$mdks))+$ewks;
$cd=(($md-$mdks)*($cdpa-$cdks)/($mdpa-$mdks))+$cdks;
$ca=(($md-$mdks)*($capa-$caks)/($mdpa-$mdks))+$caks;

//$sql="insert into ghost_surveys (md,inc,azm,tvd,vs,ew,ns,cd,ca) values ($md,$inc,$azm,$tvd,$vs,$ew,$ns,$cd,$ca)";
$sql="insert into surveys (md,inc,azm,tvd,vs,ew,ns,cd,ca,dip,fault,new,isghost) values ($md,$inc,$azm,$tvd,$vs,$ew,$ns,$cd,$ca,$dip,$fault,false,1)";
//echo $sql."<br>";
$result=$db->DoQuery($sql);
$sql = "INSERT INTO welllogs (tablename,startmd,endmd,startvs,endvs,starttvd,endtvd,startdepth,enddepth,dip,fault,scalebias,scalefactor,filter,scaleleft,scaleright,isghost) " .
						"VALUES ('wld_xxxxxx','$smd','$md','$svs','$vs','$stvd','$tvd','$sdepth','$depth',$dip,$fault,'$sbias','$sfactor',0,0,0,1);";
//echo $sql."<br>";;
$db->DoQuery($sql);
$sql = "select id,tablename from welllogs where tablename='wld_xxxxxx'";
$db->DoQuery($sql);
$db->FetchRow();
$id=$db->FetchField("id");
$tablename="wld_$id";
$real="ghost $tn trim section $smd - $md";
$query="CREATE TABLE \"$tablename\" (id serial not null primary key, md float, tvd float, vs float, value float, hide smallint not null default 0, depth float not null default 0);";
$result=$db->DoQuery($query);  
if($result!=FALSE){
	$query="UPDATE welllogs SET tablename='$tablename',realname='$real' WHERE id='$id';";
	$result = $db->DoQuery($query);
}	

$fsql = "insert into $tablename (md,tvd,vs,value,depth) values ";
$combinar = array();
$sql = "select * from ghost_data order by md asc;";
$db->DoQuery($sql);
while($row = $db->FetchRow()){
	
	$md = $db->FetchField('md');
	$vs = $db->FetchField("vs");
	$tvd = $db->FetchField("tvd");
	$gamma = $db->FetchField("value");
	$depth = $db->FetchField("depth");
	array_push($combinar,"($md,$tvd,$vs,$gamma,$depth)");
}
//print_r($combinar);
$fsql .= (implode(",",$combinar));
//echo $fsql."<br>";
$db->DoQuery($fsql);
$db->CloseDb();

echo "{\"result\":\"DONE\"}";
?>
