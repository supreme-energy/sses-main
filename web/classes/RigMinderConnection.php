<?php
class RigMinderConnection {
    function __construct($request) {
    	$this->is_connected=false;
    	$this->db_name = $request['seldbname'];
    	$this->db=new dbio("$this->db_name");
    	$query = "select * from rigminder_connection";
    	$this->db->OpenDb();
    	$this->db->DoQuery($query);
    	$row = $this->db->FetchRow();
    	if($row){
    		$this->is_connected=true;
    		$this->host = $row['host'];
    		$this->username= $row['username'];
    		$this->password = $row['password'];
    		$this->dbname=  $row['dbname'];
    		$this->aisd = $row['aisd'];
    		$this->db->CloseDb();
    	}
    }
   function verify_data_integrity(){
   		$this->db->DoQuery("SELECT * FROM surveys where plan=0 order by md");
   		$cleanup_surveys =  array();
   		$clean_up_next=false;
   		$prev_md=0;
   		
   		while($row = $this->db->FetchRow()){
   			if($row['md']>$this->aisd){
	   			if(!$clean_up_next){
	   				$query=  "select count(*) as cnt from \"Survey\" where \"MD\"=".$row['md']." and \"Inc\"=".$row['inc']." and \"Az\"=".$row['azm'].";";
	   				$this->DoQuery($query);
	   				$row2 = $this->FetchRow();
	   			} else {
	   				$row2=array('cnt'=>1);
	   			}
	   			if($clean_up_next){
	   				array_push($cleanup_surveys,array('id'=>$row['id'],'sd'=>$prev_md,'ed'=>$row['md'],'raw_srvy'=>$row));
	   			}
	   			if($row2['cnt']<=0 && !$clean_up_next){
	   				$clean_up_next=true;
	   				array_push($cleanup_surveys,array('id'=>$row['id'],'sd'=>$prev_md,'ed'=>$row['md'],'raw_srvy'=>$row));
	   			} 
   			}
   			$prev_md=$row['md'];
   		}
   	  $grp_id = false;
   	  if(count($cleanup_surveys)>0){
   	     	$query_del_group = "insert into deleted_survey_group (created) values(now())";
   	  		$this->db->DoQuery($query_del_group);
   	  		$query_get_last_id = "select id from deleted_survey_group order by id desc limit 1";
   	  		$this->db->DoQuery($query_get_last_id);
   	  		$grp_row_id = $this->db->FetchRow();
   	  		$grp_id = $grp_row_id['id'];
   	  }
   	  foreach($cleanup_surveys as $cleanup){
   	  	$raw_row=$cleanup['raw_srvy'];
   	  	$query_survy = "delete from surveys where id = ".$cleanup['id'];  	  
   	  	$query_wlg = "select id,tablename from welllogs where startmd > ".$cleanup['sd']." and endmd <=".$cleanup['ed'].";";
   	  	$this->db->DoQuery($query_wlg);
   	  	$row= $this->db->FetchRow();
   	  	if($row['tablename']){
   	  		$del_tblnme = $row['tablename'];
   	  		$wlg_id=$row['id'];
   	  		$query_drp = "DROP TABLE IF EXISTS\"$del_tblnme\";";
   	  		$query_dwlg = "delete from welllogs where id = ".$wlg_id;
   	  		$this->db->DoQuery($query_drp);
   	  		$this->db->DoQuery($query_dwlg);
   	  	}
   	  	$this->db->DoQuery($query_survy);
  		$query_del_survy  = "insert into deleted_survey_data (group_id,azm,dl,ew,inc,md,ns,tvd,vs,ca,cd,cl,dip,fault) values (" .
  			$grp_id.",".$raw_row['azm'].",".$raw_row['dl'].",".$raw_row['ew'].",".$raw_row['inc'].",".$raw_row['md'].",".
			$raw_row['ns'].",".$raw_row['tvd'].",".$raw_row['vs'].",".$raw_row['ca'].",".$raw_row['cd'].",".$raw_row['cl'].",".
			$raw_row['dip'].",".$raw_row['fault'].")";
   	  	$this->db->DoQuery($query_del_survy);
   	  }
   	  return $grp_id;
   }
   
   function prepare_las_data($sdepth=0,$edepth=100,$pass=1){  	
   	$results = array();
   	$csd = $sdepth-5;
   	$esd = $edepth+5;
   	$depth_mod=0.5;
   	$vs_mod = 0;
   	$tvd_mod= 0;
   	$this->db->OpenDb();
   	$this->db->DoQuery("SELECT endmd,scalebias,scalefactor FROM welllogs ORDER BY endmd DESC LIMIT 1;");
	$lastbias=0;
	$lastscale=1.0;
	if($this->db->FetchRow()) {
		$lastbias = $this->db->FetchField("scalebias");
		$lastscale = $this->db->FetchField("scalefactor");
	}
   	$query = "select * from surveys where plan=0 order by md desc limit 2";

   	$this->db->DoQuery($query);
   	$row = $this->db->FetchRow();
   	
   	$end_tvd = $row['tvd'];
   	$end_vs = $row['vs'];
   	$row = $this->db->FetchRow();
   
    $start_tvd = $row['tvd'];
   	$start_vs = $row['vs'];	
   	//$result  = $this->load_next_xx($sdepth,$edepth,$results,'md','0110',$pass);
   	//$result  = $this->load_next_xx($sdepth,$edepth,$results,'tvd','0111',$pass);
    //$result  = $this->load_next_xx($sdepth,$edepth,$results,'vs','0722',$pass);
   	//$result  = $this->load_next_xx($sdepth,$edepth,$results,'gmd','0821',$pass);
   	//$result  = $this->load_next_xx($sdepth,$edepth,$results,'gamma','0824',$pass); //824-Gamma Ray 1(borehole corr) 823-"Gamma Ray 1 reading"
	if($this->OpenDb()){
	$query = "select * from mod_0824 where depth>=$csd and depth <=$esd and pass=$pass order by depth asc";
	
	$this->DoQuery($query);
	$ssdepth = $sdepth+$depth_mod;
	$current_depth = $ssdepth;
	$interpol_p1_depth=0;
	$interpol_p1_gamma=0;
	$interpol_p2_depth=999999;
	$interpol_p2_gamma=0;
	$lpcnt=0;
	$row1=null;
	$row2=null;
	while($current_depth <= $edepth){
		
		if(!$row1){
			$row1=$this->FetchRow();
		}
		if(!$row2){
			$row2=$this->FetchRow();
		}

		while($row2['depth']<$current_depth){
			$row1=$row2;
			$row2=$this->FetchRow();
			if(!$row2){
				break;
			}
		}
		if(!$row2){
			break;
		}
		if($row1['depth']<$current_depth){
			
			$interpol_p1_depth=$row1['depth'];
			$interpol_p1_gamma=$row1['value'];
			
		}
		if($row2['depth']>$current_depth){
			$interpol_p2_depth=$row2['depth'];
			$interpol_p2_gamma=$row2['value'];
			
		}

		$idepth = $current_depth;
		$igamma = $interpol_p1_gamma+($interpol_p2_gamma-$interpol_p1_gamma)*(($current_depth-$interpol_p1_depth)/($interpol_p2_depth-$interpol_p1_depth));
		
		$results["$idepth"]=array("gamma"=>$igamma);
		
		$current_depth+=$depth_mod;

	}
	$gammacnt = count($results);
	$tvd_mod = ($end_tvd-$start_tvd)/$gammacnt;
	$vs_mod  = ($end_vs - $start_vs)/$gammacnt;
	$stvd = $start_tvd+$tvd_mod;
	$svs  = $start_vs+$vs_mod;
	$current_tvd=$stvd;
	$current_vs = $svs;
	foreach($results as $key=>$value){
		$value['tvd']=$current_tvd;
		$value['vs']=$current_vs;
		$results[$key]=$value;
		$current_tvd+=$tvd_mod;
		$current_vs +=$vs_mod;
	}
	$query = "INSERT INTO welllogs (tablename) VALUES ('wld_xxxxxx');";
    $result = $this->db->DoQuery($query);
    if($result==FALSE) die("<pre>Database error attempting to insert a new welllog information block\n</pre>");
    $query = "select id,tablename from welllogs where tablename='wld_xxxxxx'";
    $this->db->DoQuery($query);
    if($this->db->FetchRow()){
    	$id = $this->db->FetchField("id");
    	$tablename="wld_$id";
    	$real="RigMinder Auto Import $sdepth - $edepth";
    	$query="CREATE TABLE \"$tablename\" (id serial not null primary key, md float, tvd float, vs float, value float, hide smallint not null default 0, depth float not null default 0);";
    	$result=$this->db->DoQuery($query);
    	if($result!=FALSE){
 		$query="UPDATE welllogs SET tablename='$tablename',realname='$real' WHERE id='$id';";
		$result=$this->db->DoQuery($query);   		
    	}
    } else die("<pre>Id for new table entry not found!\n</pre>");
    if($result==FALSE) {
		if($id!="")$db->DoQuery("DELETE FROM welllogs WHERE id='$id';");
		$db->DoQuery("DROP TABLE IF EXISTS\"$tablename\";");
		die("<pre>Database error attempting to create table: $tablename\n</pre>");
	}
	$this->db->DoQuery("BEGIN TRANSACTION;");
	$datacnt=0;
	foreach($results as $key=>$value){
		$md = $key;
		$val= round($value['gamma'],2);
		$tvd =round($value['tvd'],2);
		$vs  =round($value['vs'],2);
		$depth=$value['tvd'];
		$query="INSERT INTO \"$tablename\" (md,value,tvd,vs,depth) VALUES ($md,$val,$tvd,$vs,$depth);";
		$result = $this->db->DoQuery($query);
		if($result==FALSE) {
			$this->db->DoQuery("ROLLBACK;");
			die("<pre>Error updating table: $tablename\n</pre>");
		}
		$datacnt++;
	}

	$this->db->DoQuery("COMMIT;");
	if($datacnt<=0) {
		$this->db->DoQuery("DELETE FROM welllogs WHERE id=$id;");
		$this->db->DoQuery("DROP TABLE \"$tablename\";");
		$this->db->CloseDb();
		$tablename="";
	}
	else {
		$this->db->DoQuery("BEGIN TRANSACTION;");
		$this->db->DoQuery("UPDATE welllogs SET startdepth='$stvd',enddepth='$end_tvd' WHERE id='$id';");
		$this->db->DoQuery("UPDATE welllogs SET startmd='$ssdepth',endmd='$edepth' WHERE id='$id';");
		$this->db->DoQuery("UPDATE welllogs SET startvs='$svs',endvs='$end_vs' WHERE id='$id';");
		$this->db->DoQuery("UPDATE welllogs SET starttvd='$stvd',endtvd='$end_tvd' WHERE id='$id';");
		$this->db->DoQuery("UPDATE welllogs SET scalebias='$lastbias',scalefactor='$lastscale' WHERE id='$id';");
		$this->db->DoQuery("UPDATE welllogs SET fault='0',dip='0' WHERE id='$id';");
		$this->db->DoQuery("UPDATE welllogs SET filter='0',scaleleft='0',scaleright='0' WHERE id='$id';");
		$this->db->DoQuery("UPDATE appinfo set tablename='$tablename';");
		$this->db->DoQuery("delete from projections where ptype='sld'");
		$result=$this->db->DoQuery("COMMIT;");
		if($result==FALSE) die("<pre>Bad bad errors on COMMIT: welllogs\n</pre>");

		// added this part to calculate the dip automatically

		require_once '../GetCalculatedDip.php';
		$dip = '';
		GetCalculatedDip($this->db,$dip);
		if($dip != '' and is_numeric($dip))
			$this->db->DoQuery("UPDATE welllogs SET dip='$dip' WHERE id='$id'");
	}
		
	exec("../sses_gva -d ".$this->db_name);
	exec("../sses_cc -d ".$this->db_name);
	exec("../sses_cc -d ".$this->db_name." -p");
	exec("../sses_af -d $seldbname");   	
	$this->CloseDb();
	}
	$this->db->CloseDB();
   }
   
   function load_next_survey($load=false){
   		if($this->dbname){
   		if($this->OpenDb()){
	   		$this->db->OpenDb();
	   		$cleanup_occured = $this->verify_data_integrity();
	   		$query = "select * from \"Survey\" where \"MD\" > ".$this->aisd." order by \"MD\" asc";
	   		$this->DoQuery($query);
	   		$all_surverys_loaded =true;
	   		$new_survey_found = false;
	   		$lastmd = 0; 
	   		while($row=$this->FetchRow()){
	   			if($new_survey_found){
	   				$all_surverys_loaded = false;
	   				break;
	   			}
	   			$md = $row['MD'];
	   			$inc = $row['Inc'];
	   			$azm = $row['Az'];
	   			$ts  = $row['time'];
	   			//$vs  = $row['VS'];
	   			//$ns  = $row['NS'];
	   			//$ew  = $row['EW'];
	   			//$dl  = $row['DL'];
	   			//$ca  = $row['CA'];
	   			//$cd  = $row['CD'];
	   			//$tvd = $row['TVD'];
	   			$nquery = "select * from surveys where azm=$azm" .
	   					" and md=$md" .
	   					" and inc=$inc";
				$this->db->DoQuery($nquery);
				if($this->db->FetchRow()){
					$lastmd=$row['MD'];
					continue;
				} else {
					if($load){
						$nquery = "insert into surveys (azm,inc,md,srcts) values ($azm,$inc,$md,$ts)";
						$this->db->DoQuery($nquery);
					}
					$new_survey_found=true;
				}   					
				
	   		}
	   	$this->db->CloseDb();	
	   	$this->CloseDb();
	   	
	   	if($load){
	   		$seldbname=$this->db_name;
	   		exec("../sses_gva -d $seldbname",$output);
	   		exec("../sses_cc -d $seldbname",$output);	
	   		$this->prepare_las_data($lastmd,$md,1);
	   	}
	   	if( $new_survey_found){
	   		return array("next_survey"=>true,"md"=>$md,"inc"=>$inc,"azm"=>$azm,"cleanup_occured"=>$cleanup_occured);
	   	} else {
	   		return array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"cleanup_occured"=>$cleanup_occured);;
	   	}
	   	}else{
	   		return array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"msg"=>'RigMinder DB Table Name not acessible, verify rigminder endpoint,db name,username and password.');;
	   	}
   	}else{
   		return array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"msg"=>'RigMinder DB Table Name Not set');;
   	}
   	
   }
   
   function OpenDb() {
		try {
			$this->dbh = new PDO("pgsql:host=$this->host; dbname=$this->dbname",
				$this->username, $this->password);
		}
		catch(PDOException $e) {
			//echo $e->getMessage();
			return 0;
		}
		return 1;
	}

	function CloseDb() {
		$this->dbh = null;
	}

	function DoQuery($qbuf) {
		$q=$qbuf;
		// special case for MySQL equivilent
		if(stripos($q, "SHOW TABLES")!==false)
			$q = "select tablename from pg_tables where tablename !~'^pg_+' AND tablename !~'^sys_+' ORDER BY tablename;";
		try {
			$this->query=$q;
			// $this->stmt=$this->dbh->query($q);
			$this->stmt=$this->dbh->prepare($q);
			$this->stmt->execute();
			$err=$this->stmt->errorCode();
			if($err != 0) {
				print "Error $err in query: ";
				echo $this->query;
				print "<br>";
				// echo $this->stmt->errorInfo();
				// print "<br>";
			}
			return $this->stmt->errorCode();
		}
		catch(PDOException $e) {
			echo $e->getMessage();
			return 1;
		}
	}

	function FetchRow($rownum=NULL) {
		$this->result=$this->stmt->fetch(PDO::FETCH_ASSOC);
		return $this->result;
	}

	function FetchField($fieldname) {
		$retval="";
		foreach($this->result as $key=>$val) {
			if($key==$fieldname) {
				$retval=$val;
				break;
			}
	    }
		return $retval;
	}

	function FetchNumRows() {
		return $this->stmt->rowCount();
	}

	function FetchFieldNum($num, $fieldname) {
		$this->DoQuery($this->query);
		$retval="$fieldname not found";
		$cnt=$this->FetchNumRows();
		for($i=0; $i<=$num && $i<$cnt; $i++) {
			$this->FetchRow();
			if($i==$num)
				$retval=$this->FetchField($fieldname);
		}
		return $retval;
	}

	function FreeResult() {
	}

	function ColumnExists($tn, $col) {
		$this->query=sprintf("select column_name from information_schema.columns where table_name='%s';", $tn);
		$this->DoQuery($this->query);
		$num=$this->FetchNumRows();
		for($i=0; $i<$num; $i++) {
			$this->FetchRow();
			$name=$this->FetchField("column_name");
			if($name==$col)
				return 1;
		}
		return 0;
	}
	function TableExists($tn) {
		$this->query=sprintf("select * from pg_tables where schemaname='public' and tablename='%s';", $tn);
		$this->DoQuery($this->query);
		return $this->FetchNumRows();
	}
	
}
?>
