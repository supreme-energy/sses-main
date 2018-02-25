<?php 
require_once("dbio.class.php");
$seldbname=$_REQUEST['seldbname'];
$db=new dbio($seldbname,true);
$db->OpenDb();
$data = json_decode(file_get_contents('php://input'), true);
print_r($data);
$db->DoQuery("SELECT endmd,scalebias,scalefactor FROM welllogs ORDER BY endmd DESC LIMIT 1;");
$lastbias=0;
$lastscale=1.0;
if($db->FetchRow()) {
	$lastendmd = $db->FetchField("endmd");
	$lastbias = $db->FetchField("scalebias");
	$lastscale = $db->FetchField("scalefactor");
}

$data_index = 0;
$total_indexes = count($data);

foreach($data as $survey){
	if($data_index >= $total_indexes-1){
		break;
	}
	$md = $survey['md'];
	
	$startmd = $survey['depth'][1];
	$endmd   = $data[$data_index+1]['depth'][0];
	
	$azm = $survey['azm'];
	$inc = $survey['inc'];
	$vs  = $survey['vs'];
	
	$startvs = $survey['vss'][1];
	$endvs   = $data[$data_index+1]['vss'][0];
	
	$tvd = $survey['tvd'];
	
	$starttvd = $survey['tvds'][1];
	$endtvd = $data[$data_index+1]['tvds'][0];
	
	$db->DoQuery("select * from surveys where md=$md and inc=$inc and azm=$azm");
	$row = $db->FetchRow();
	if($row){
		
	} else{
		$db->DoQuery("INSERT INTO surveys (md,inc,azm) VALUES ($md,$inc,$azm)");
		$db->DoQuery("UPDATE wellinfo SET pamethod='-1';");
		$db->DoQuery("delete from projections where ptype='rot' or ptype='sld'");
	}
	//check for wellog row
	$db->DoQuery("select * from welllogs where startmd=$startmd");
	$welllog_row = $db->FetchRow();
	if($welllog_row){
		$tablename = $welllog_row['tablename'];
	} else{
	  $result = $db->DoQuery("insert into welllogs (tablename,startdepth,enddepth,startmd,endmd,startvs,endvs,starttvd,endtvd,scalebias,scalefactor,fault,dip,filter,scaleleft,scaleright) 
			values ('xxxxxx',$starttvd,$endtvd,$startmd,$endmd,$startvs,$endvs,$starttvd,$endtvd,$lastbias,$lastscale,0,0,0,0,0)");
	  
	  if($result==FALSE) die("<pre>Database error attempting to insert a new welllog information block\n</pre>");
	  
	  $db->DoQuery("SELECT id,tablename FROM welllogs WHERE tablename='xxxxxx';");
	  if($db->FetchRow()) {
	  	$id = $db->FetchField("id");
	  	$tablename="wld_$id";
	  	$query = "CREATE TABLE \"$tablename\" (id serial not null primary key, md float, tvd float, vs float, value float, hide smallint not null default 0, depth float not null default 0);";
	  	$result = $db->DoQuery($query);
	  	if($result!=FALSE){
	  		$query="UPDATE welllogs SET tablename='$tablename',realname='WellLog Data Import' WHERE id='$id';";
	  		$result=$db->DoQuery($query);
	  	}
	  }else die("<pre>Id for new table entry not found!\n</pre>");
	  if($result==FALSE) {
	  	if($id!="") $db->DoQuery("DELETE FROM welllogs WHERE id='$id';");
	  	$db->DoQuery("DROP TABLE IF EXISTS\"$tablename\";");
	  	die("<pre>Database error attempting to create table: $tablename\n</pre>");
	  }
	  
	  
	}
	
	//check for data populated in table
	$query = "select count(*) as cnt from $tablename";
	$db->DoQuery($query);
	$data_entry_cnt = $db->FetchRow();
	if($data_entry_cnt['cnt']==0){
		$db->DoQuery("BEGIN TRANSACTION;");
		for($i = 1; $i < count($survey['gamma']); $i++){
			$md = $survey['depth'][$i];
			$gamma = $survey['gamma'][$i];
			$tvd   = $survey['tvds'][$i];
			$vs = $survey['vss'][$i];
			$inc = $survey['inc'];
			$azm = $survey['azm'];
			
			if($gamma > -999.0){
				$query = "INSERT INTO \"$tablename\" (md,value,tvd,vs,depth) VALUES ($md,$gamma,$tvd,$vs,$md);";
				echo $query. "\n";
				$result=$db->DoQuery("INSERT INTO \"$tablename\" (md,value,tvd,vs,depth) VALUES ($md,$gamma,$tvd,$vs,$md);");
			}
			if($result==FALSE) {
				$db->DoQuery("ROLLBACK;");
				die("<pre>Error updating table: $tablename\n</pre>");
			}
		}
		$md = $data[$data_index+1]['depth'][0];
		$gamma = $data[$data_index+1]['gamma'][0];
		$tvd   = $data[$data_index+1]['tvds'][0];
		$vs = $data[$data_index+1]['vss'][0];
		$inc = $data[$data_index+1]['inc'];
		$azm = $data[$data_index+1]['azm'];
		if($gamma > -999.0){		
			$result=$db->DoQuery("INSERT INTO \"$tablename\" (md,value,tvd,vs,depth) VALUES ($md,$gamma,$tvd,$vs,$md);");
		}
		$result=$db->DoQuery("COMMIT;");
	}
	
	$data_index++;
}
exec("./sses_gva -d $seldbname");
exec("./sses_cc -d $seldbname");
exec("./sses_cc -d $seldbname -p");
exec("./sses_af -d $seldbname");
?>