<?
    function polaris_sort($a,$b){
        $a= $a['md'];
        $b= $b['md'];
        if($a== $b){
            return 0;
        }
        return ($a<$b)? -1:1;
    }
	class LasFileConnection  {

	    function __construct($request) {
	        $this->db_name = $request['seldbname'];
	        $this->raw_request = $request;
	        $this->get_witsml_data();
	        $this->debug = (isset($request['debug']) ? true : false);
	        $this->db=new dbio("{$this->db_name}");
	        $this->db2=new dbio("{$this->db_name}");
	        print $this->debug;
	    }
	    
	    function get_witsml_data($field='endpoint'){
	        $this->client=null;
	        $intdb = new dbio($this->db_name);
	        $intdb->OpenDb();
	        $intdb->DoQuery("SELECT * FROM witsml_details order by id desc limit 1");
	        if($row=$intdb->FetchRow()){
	            $this->endpoint=$row['endpoint'];
	            $this->send_data =$row['send_data'];
	            $this->username=$row['username'];
	            $this->password=$row['password'];
	            $this->well = $row['wellid'];
	            $this->wellbore = $row['boreid'];
	            $this->logid = $row['logid'];
	            $this->trajectory = $row['trajid'];
	        }else{
	            $this->endpoint='';
	            $this->send_data=false;
	            $this->username='';
	            $this->password='';
	            $this->well = '';
	            $this->wellbore = '';
	            $this->trajectory = '';
	        }
	        $intdb->DoQuery("select * from rigminder_connection");
	        
	        if($row=$intdb->FetchRow()){
	            $this->aisd = $row['aisd'];
	            if($this->endpoint==''){
	                $this->endpoint =$row['host'];
	                $this->username = $row['username'];
	                $this->password = $row['password'];
	            }
	        } else {
	            $this->aisd=0;
	        }
	        $intdb->DoQuery("select auto_gr_mnemonic from appinfo;");
	        if($row=$intdb->FetchRow()){
	            $this->grmnemonic=$row['auto_gr_mnemonic'];
	        } else {
	            $this->grmnemonic="GR";
	        }
	        $intdb->CloseDb();
	        return $this->$field;
	    }
	    
		function smooth_range($sval,$eval,$increment,$shift_first=true){
			$perincrement = ($eval-$sval)/$increment;
			$vals = array();
			for($i=0;$i<$increment;$i++){
			
				array_push($vals,$sval);
				$sval=$sval+$perincrement;
				
			}
			if($shift_first){
				array_shift($vals);
			}
			array_push($vals,$eval);
			return $vals;
		}
		
		function drop_empties($dataset,$cols){
			$new_data_ar = array();
			foreach($dataset as $delement){				
				$exploded = $delement;
				$grval = $exploded[$cols[$this->grmnemonic][2]-1];
				if($grval!=''){
				    array_push($new_data_ar,$delement);
				}
				
			}
			return $new_data_ar;
		}
		
		function smooth_gr($dataset,$cols,$intlgr,$shift_first=true){
				$lastgr=-1;
				$smooth_start_idx=-1;
				$smooth_end_idx = -1;
				$idx=0;				
				$smoothedgr=array();
				//echo "start size of dataset:".count($dataset->data)."\n";
				//first go through the data set and replace all null values 0, negative or no value with the last GR value
				$newdataset=array();
				foreach($dataset as $delement){
										
					$exploded = $delement;
					$grval = $exploded[$cols[$this->grmnemonic][2]-1];
					//print "unmodified grval: ".$grval."\n";
					if($grval <=0 || $grval==''){
						$grval=$intlgr;
					}
					array_push($newdataset,$grval);
					$intlgr=$grval;
				}
				
				//print_r($newdataset);
				foreach( $newdataset as $grval){
					
				//	print "curgr:$grval\n";
				//	print "lastgr:$lastgr\n";
					
					if($grval==$lastgr){
						if($smooth_start_idx==-1){
							$smooth_start_idx=$idx-1;
						}
					}else if($smooth_start_idx !=-1){
						if($smooth_end_idx==-1){
							$smooth_end_idx=$idx;
						}
					}
				//	print "ssi:$smooth_start_idx\n";
				//	print "sse:$smooth_end_idx\n";
				//	print "array(".count($smoothedgr)."):";
					$pidx=0;
				//	foreach($smoothedgr as $v){
						
			//			print "(".$pidx.")".$v.",";
				//		$pidx++; 
				//	}
				//	print"\n";
					if($smooth_start_idx!=-1){
						if($smooth_end_idx != -1){						
							
							$sval = $newdataset[$smooth_start_idx];
							$eval = $newdataset[$smooth_end_idx];
						
							$lenval = ($smooth_end_idx-$smooth_start_idx);
							//if($this->autorc_type=="digidrill"){
							//	$smoothedgr=array_merge($smoothedgr,$this->smooth_range($sval,$eval,$lenval,false));
							//} else {
								$smoothedgr=array_merge($smoothedgr,$this->smooth_range($sval,$eval,$lenval,true));
							//}
							
	
							$smooth_start_idx=-1;
							$smooth_end_idx = -1;
						}
					} else {					
						array_push($smoothedgr,$grval);
					}
					$lastgr=$grval;
					
					//print "--------------------\n";
					$idx ++;
				}
				//echo "idx at loop end:".$idx."\n";
				//account for the condition underwhich smoothing doesn't end before reaching the end of the dataset
				if(count($dataset) != count($smoothedgr)){				
					if($this->debug){
						print "pre final smooth set";
						print count($smoothedgr);
						print_r($smoothedgr);
					}
					$smooth_end_idx = count($dataset->data)-1;
					$smooth_start_idx = count($smoothedgr)-1;
					$sval = $newdataset[$smooth_start_idx];
					//echo $sval ."\n";
					$eval  = $newdataset[$smooth_end_idx];
					if(!$eval){
						$eval=$sval;
					}
					//echo $eval."\n";;
					$lenval = ($smooth_end_idx-$smooth_start_idx);
				//	echo $lenval ."\n";;
					//if($this->autorc_type=="digidrill"){
					//	$smoothedgr=array_merge($smoothedgr,$this->smooth_range($sval,$eval,$lenval,false));
					//} else {
					$smoothedgr=array_merge($smoothedgr,$this->smooth_range($sval,$eval,$lenval,true));
					//}
				}
				if(count($dataset)>count($smoothedgr)){
					while(count($dataset)>count($smoothedgr)){
						array_push($smoothedgr,$lastgr);
					}
				}
				
			//	echo count($dataset->data)."\n";
				//echo count($smoothedgr)."\n";
				
			//	foreach($smoothedgr as $v){
			//			
			//			print "(".$pidx.")".$v.",";
			//			$pidx++; 
			//	}
			//	print"\n";
				return $smoothedgr;
		}
		
		function retreive_log_headers(){
		    $filename="/tmp/custom_import_".$this->db_name.".las";
		    $body = "";
		    $infile=fopen("$filename", "r");
		    if(!$infile)	{
		        return;
		    }
		    
		    do {
		        $line=fgets($infile,1024);
		        if($line==FALSE){
		            echo json_encode(array('status'=>'error', 'message'=>"End of file looking for ~A data section"));
		            exit;
		        }
		    } while(stristr($line, "~Curve")==FALSE);
		    
		    $final = array();
		    do {
		        $line=fgets($infile,1024);
		        $line=trim($line);
		        $line=preg_replace( '/\s+/', ',', $line );
		        $r = explode(",", $line);
		        if($r[0]=='GAMA.API'){
		            array_push($final, 'GR');
		        } else if($r[0]=='DEPT.ft') {
		            array_push($final,'Mdepth');
		        } else if($r[0] && $r[0]!='~Ascii'){
		            array_push($final,$r[0]);		            
		        }
		    } while(stristr($line, "~A")==FALSE);
		    
		    fclose($infile);		    
		    		    
		    return $final;
		}
		function retrieve_log_file($sdepth, $edepth){
		    $filename="/tmp/custom_import_".$this->db_name.".las";
		    $body = "";
		    $infile=fopen("$filename", "r");
		    if(!$infile)	{
		        return;		        
		    }
		    
		    do {
		        $line=fgets($infile,1024);
		        if($line==FALSE){
		            echo json_encode(array('status'=>'error', 'message'=>"End of file looking for ~A data section"));
		            exit;
		        }
		    } while(stristr($line, "~A")==FALSE);
		    
		    while($line=fgets($infile,1024)) {
		        $line=trim($line);
		        $line=preg_replace( '/\s+/', ',', $line );
		        $line_ex = explode(",",$line);
		        $md = floatval($line_ex[0]);
		        if($md <= $sdepth){
		            continue;
		        }
		        if($md > $edepth){
		          break;
		        }
		        $body.="$line\n";
		    }
		    fclose($infile);
		    return $body;
		}
		
		function prepare_las_data($sdepth=0,$edepth=100,$pass=1,$include_additional=false,$dip=false,$fault=false){
			
		    $resp = $this->retrieve_log_file($sdepth,$edepth);
			$headers = $this->retreive_log_headers();
			
			$csv_data = str_getcsv($resp);						
			if($this->debug){
			    echo "headers data \n";
			    print_r($headers);
			    echo "csv data \n";
			    print_r($csv_data);			    
			}
			
			$cols = array();
			$data = array();
			$i=0;
			$this->db->OpenDb();
			$headersmnemo=array();						
			foreach($headers as $column){
				$edata = array();
				$mnemo =$column;
				if(isset($cols["$mnemo"])){
					continue; 
				}
				array_push($edata,$i);
				array_push($headersmnemo,$mnemo);
				$query ="select * from edatalogs where label='".$column."';";
				$result = $this->db->DoQuery($query);
				$row = $this->db->FetchRow();
				if($row){
					array_push($edata,$row['tablename']);
				} else {
					$query = "insert into edatalogs (tablename,label,scalelo,scalehi,enabled,color) values ('edl_xxx','".$column."',0,300,0,'000000')";
					$this->db->DoQuery($query);
					$query = "select * from edatalogs where tablename='edl_xxx'";
					$this->db->DoQuery($query);
					if($this->db->FetchRow()){
						$elog_id = $this->db->FetchField('id');
						$elog_tablename="eld_".$elog_id;
						$query = "CREATE TABLE \"$elog_tablename\" (id serial not null primary key,md float,tvd float,vs float,value float)";
						$result=$this->db->DoQuery($query);
						if($result!=FALSE){
							$query="UPDATE edatalogs set tablename='$elog_tablename' where id='$elog_id'";
							echo $query."\n";
							$this->db->DoQuery($query);
						}
						array_push($edata,$elog_tablename);
					}
				}
				array_push($edata,$i);
				$cols["$mnemo"]=$edata;
				$i++;
			}
			print_r($cols)
			$wllastquery = "select * from welllogs order by id desc limit 1";
			$result = $this->db->DoQuery($wllastquery);
			$lastwellog= $this->db->FetchRow();
			$query = "select * from welllogs where startmd=$sdepth and endmd=$edepth";
			$result=$this->db->DoQuery($query);
			$welllogexists=$this->db->FetchRow();
			if(!$welllogexists){
				if($lastwellog){
					$lastbias = $lastwellog['scalebias'];
					$lastscale = $lastwellog['scalefactor'];
				} else {
					$lastbias=0;
					$lastscale=1.0;
				}
				$query = "INSERT INTO welllogs (tablename) VALUES ('wld_xxxxxx');";
				$result = $this->db->DoQuery($query);
				if($result==FALSE) die("<pre>Database error attempting to insert a new welllog information block\n</pre>");
				$query = "select id,tablename from welllogs where tablename='wld_xxxxxx'";
	    		$this->db->DoQuery($query);
			    if($this->db->FetchRow()){
					$id = $this->db->FetchField("id");
					$tablename="wld_$id";
					$real="LasFile Auto Import $sdepth - $edepth";
					$query="CREATE TABLE \"$tablename\" (id serial not null primary key, md float, tvd float, vs float, value float, hide smallint not null default 0, depth float not null default 0);";
					$result=$this->db->DoQuery($query);
					if($result!=FALSE){
						$query="UPDATE welllogs SET tablename='$tablename',realname='$real' WHERE id='$id';";
						$result=$this->db->DoQuery($query);   		
					}
				} else die("<pre>Id for new table entry not found!\n</pre>");
			} else {
				$id=$welllogexists['id'];
				$tablename=$welllogexists['tablename'];
				$real =$welllogexists['realname'];
				$lastbias = $welllogexists['scalebias'];
				$lastscale = $welllogexists['scalefactor'];
				$query= "delete from \"$tablename\"";
				$this->db->DoQuery($query);
			}

			$datacnt = 0;
			$stvd = null;
			$ssdepth=$sdepth;
			$svs=null;
			//print_r($cols);
			$curpos=0;
			//echo "lenght before smoothig calls:".count($xmlout->log->logData->data);
			
			$use_data = $this->drop_empties($csv_data,$cols);
			$smooth_range = count($use_data);
			if(count($use_data)==0){
			    $smooth_range =count($csv_data);
			}
			if(!$this->current_r_survery || !$this->last_r_survey){
				$this->simple_load_last_current();
			}

			$tvdr = $this->smooth_range($this->last_r_survey['tvd'],$this->current_r_survey['tvd'],$smooth_range);
			
			$vsr  = $this->smooth_range($this->last_r_survey['vs'],$this->current_r_survey['vs'],$smooth_range);
			$query= "select tablename from welllogs where endmd <= ".$this->last_r_survey['md']." order by endmd desc limit 1";
			$this->db->DoQuery($query);
			$row = $this->db->FetchRow();
			$query = "select value from ".$row['tablename']." order by md desc limit 1";
			$this->db->DoQuery($query);
			$row = $this->db->FetchRow();
			$intlgr = $row['value'];
			if($this->raw_request['debug']){
				print_r($use_data);
			}
			$grr  = $this->smooth_gr($use_data,$cols,$intlgr);
			$rawdata_ar=array();	
			if($this->raw_request['debug']){
				print_r($grr);
			}
			
			foreach($csv_data as $delement){
				
				$val = implode(',',$delement);
				array_push($rawdata_ar,$val);
				$exploded = $delement;
				$md =  $exploded[$cols['Mdepth'][2]];
				if(!$md){$md = $exploded[$cols['DEPTH'][2]];}
				if(!$md){$md = $exploded[$cols['TOT_DPT_MD'][2]];}
				$val = round($grr[$curpos],2);
				$vs = round($vsr[$curpos],2);
				$tvd =$tvdr[$curpos];
				$curpos++;
				foreach($cols as $edata_log){
					$edatalog_tn = $edata_log[1];
					$edlog_val = $exploded[$edata_log[2]-1];
					//$query = "select * from \"$edatalog_tn\" where md=$md and tvd=$tvd and vs=$vs and value=$edlog_val";
					//echo $query."\n";
					//$this->db->DoQuery($query);
					//if(!$this->db->FetchRow()){
					$this->db->DoQuery("BEGIN TRANSACTION;");
						if($edlog_val){
							$query = "insert into \"$edatalog_tn\" (md,tvd,vs,value) values ($md,$tvd,$vs,$edlog_val)";
							echo $query."\n";
							$result = $this->db->DoQuery($query);
							if($result==FALSE) {
								echo "rollback";
								$this->db->DoQuery("ROLLBACK;");
							}
						}
					$this->db->DoQuery("COMMIT;");						
					//}
				}
			}
			$this->db->DoQuery("COMMIT;");
			$curpos=0;
			foreach( $use_data as $delement){
				$val = implode(',',$delement);
				array_push($rawdata_ar,$val);
				$exploded = $delement;
				$md =  $exploded[$cols['Mdepth'][2]];
				if(!$md){$md = $exploded[$cols['DEPTH'][2]];}
				if(!$md){$md = $exploded[$cols['TOT_DPT_MD'][2]];}
				$val = round($grr[$curpos],2);
				$vs = round($vsr[$curpos],2);
				$tvd =$tvdr[$curpos];
				$curpos++;
				if($svs==null){
					$svs = $vs;
				}
				if($stvd==null){
					$stvd=$tvd;
				}
				$depth = $tvd;
				$tvd = round($tvd,2);
				$query="INSERT INTO \"$tablename\" (md,value,tvd,vs,depth) VALUES ($md,$val,$tvd,$vs,$depth);";
				echo "$query\n";
				$result = $this->db->DoQuery($query);
				if($result==FALSE) {
					echo "rollback";
					$this->db->DoQuery("ROLLBACK;");
					die("<pre>Error updating table: $tablename\n</pre>");
				}
				$datacnt++;
			}
			$end_tvd=$tvd;
			$end_vs=$vs;
			$final_rawdata_str = implode(',',$headersmnemo).PHP_EOL.(implode(PHP_EOL,$rawdata_ar));
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
				$this->db->DoQuery("UPDATE welllogs SET raw_import_data='$final_rawdata_str' WHERE id='$id';");
				$this->db->DoQuery("UPDATE welllogs SET scalebias='$lastbias',scalefactor='$lastscale' WHERE id='$id';");
				if($dip===false){
					$this->db->DoQuery("UPDATE welllogs SET fault='0',dip='0' WHERE id='$id';");
				}else {
					$this->db->DoQuery("UPDATE welllogs SET fault='$fault',dip='$dip' WHERE id='$id';");
				}
				$this->db->DoQuery("UPDATE welllogs SET filter='0',scaleleft='0',scaleright='0' WHERE id='$id';");
				$this->db->DoQuery("UPDATE appinfo set tablename='$tablename';");
				$this->db->DoQuery("delete from projections where ptype='sld'");
				$result=$this->db->DoQuery("COMMIT;");
				if($result==FALSE) die("<pre>Bad bad errors on COMMIT: welllogs\n</pre>");
			}
			exec(__DIR__ ."/../sses_gva -d ".$this->db_name);
			exec(__DIR__ ."/../sses_cc -d ".$this->db_name);
			exec(__DIR__ ."/../sses_cc -d ".$this->db_name." -p");
			exec(__DIR__ ."/../sses_af -d $this->db_name"); 
			$this->db->CloseDb();
		}
	function cleanup_data($cleanup_surveys){
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
   	  	$this->db->DoQuery($query_survy);
  		$query_del_survy  = "insert into deleted_survey_data (group_id,azm,dl,ew,inc,md,ns,tvd,vs,ca,cd,cl,dip,fault) values (" .
  			$grp_id.",".$raw_row['azm'].",".$raw_row['dl'].",".$raw_row['ew'].",".$raw_row['inc'].",".$raw_row['md'].",".
			$raw_row['ns'].",".$raw_row['tvd'].",".$raw_row['vs'].",".$raw_row['ca'].",".$raw_row['cd'].",".$raw_row['cl'].",".
			$raw_row['dip'].",".$raw_row['fault'].")";
   	  	$this->db->DoQuery($query_del_survy);
   	  }
   	  //cleanup all data
   	  $query = "select * from surveys where plan = 0 order by md desc limit 1";
   	  $this->db->DoQuery($query);
   	  $r = $this->db->FetchRow();
   	  $query =" select id, tablename from welllogs where startmd > ".$r['md'];
   	  $this->db->DoQuery($query);
   	  $this->db2->OpenDb();
   	  while($r2 = $this->db->FetchRow()){
   	  	 if($r2['tablename']){
   	  		$del_tblnme = $r2['tablename'];
   	  		$wlg_id=$r2['id'];
   	  		$query_drp = "DROP TABLE IF EXISTS\"$del_tblnme\";";
   	  		$query_dwlg = "delete from welllogs where id = ".$wlg_id;
   	  		$this->db2->DoQuery($query_drp);
   	  		$this->db2->DoQuery($query_dwlg);
   	  	}
   	  }
   	  $query_elg = "select id,tablename from edatalogs";
   	  $this->db->DoQuery($query_elg);
   	  while($row=$this->db->FetchRow()){
   	  		$elgtn=$row['tablename'];
   	  		$query_delelg = "delete from \"$elgtn\" where md > ".$r['md'];
   	  		$this->db2->DoQuery($query_delelg);
   	  }
   	  $this->db2->CloseDb();
   	  $this->db->CloseDb();
   	  return $grp_id;
	}	
	function verify_data_integrity($do_cleanup=false){
   		$this->db->OpenDb();
   		$this->db->DoQuery("SELECT * FROM surveys where plan=0 order by md");
   		$cleanup_surveys =  array();
   		$clean_up_next=false;
   		$prev_md=0;
   		
   		while($row = $this->db->FetchRow()){
   			if($row['md']>$this->aisd){
	   			if(!$clean_up_next){
	   				foreach($this->surveys as $s){
	   					if($s['md']==$row['md'] && $s['inc']==$row['inc']&&$s['azm']==$row['azm']){
	   						$row2=array('cnt'=>1);
	   						break;
	   					}else{
	   						$row2=array('cnt'=>0);
	   					}
	   				}
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
   	  if($do_cleanup){
   	  	$grp_id=$this->cleanup_data($cleanup_surveys);
   	  	return $grp_id;
   	  } else {
   	  	$this->cleanup_surverys=$cleanup_surveys;
   	  	return count($cleanup_surveys)>0; 	
   	  }
   	  
   	  
   }
   
   function get_survey_from_file(){
       $filename="/tmp/survey_import_".$this->db_name.".las";
       $tempf = fopen($filename, 'r');
       $surveys = array();
       while (($data = fgetcsv($tempf, 5000, ",")) !== FALSE) {
           $md = $data[1];
           $inc = $data[2];
           $azm = $data[3];
           $time = $data[0];
           array_push($surveys,array('md'=>$md,'inc'=>$inc,'azm'=>$azm,'time'=>$time));
       }		
		usort($surveys,"polaris_sort");
		fclose($tempf);
		return $surveys;
   }
   
   function load_surveys_in_range($startmd,$endmd,$load_best_dip_fault=false){
   		if($this->db_name){
   			$this->db->OpenDb();
   			$pterm_query = "select pterm_method from wellinfo";
   			$this->db->DoQuery($pterm_query);
   			$pterm_Row = $this->db->FetchRow();
   			$pterm_method = $pterm_Row['pterm_method'];
   			$pterm_query = "update wellinfo set pterm_method = 'bc';";
   			$this->db->DoQuery($pterm_query);
   			$surveys = $this->get_polaris_survey();
   			$this->surveys=$surveys;
   			
   			$query = "select * from surveys where plan=0 and md < $startmd order by md desc;";
   			$this->db->DoQuery($query);
   			$resrow = $this->db->FetchRow();
   			if(!$resrow){
   				$lastmd =$this->aisd;
   				$svycnt=0;
   			} else {
   				$lastmd = $resrow['md'];
   				$svycnt=1;
   			}
   			$deleted_surveys=array();
   			if($load_best_dip_fault!==false){
   				$query = "select * from deleted_survey_data where group_id=$load_best_dip_fault";
   				$this->db->DoQuery($query);
   				while($resrow = $this->db->FetchRow()){
   					array_push($deleted_surveys,$resrow);
   				}
   				
   			}
   			$this->dsurveys= $deleted_surveys;
   			$seldbname=$this->db_name;
   			$spos=0;
   			$loaded=array();
   			$queryl = "select * from surveys where plan=0 order by md asc limit 1";
			$this->db->DoQuery($queryl);
			$this->last_r_survey=$this->db->FetchRow();
   			foreach($this->surveys as $survey){
   				$md = $survey['md'];
   				if($md >= $startmd && $md <=$endmd){
   							$this->db->OpenDb();
   							$svycnt++;
		   					$inc = $survey['inc'];
		   					$azm = $survey['azm'];
		   					//$ts = strtotime($survey['time']);
		   					$nquery = "select * from surveys where azm=$azm" .
		   					" and md=$md" .
		   					" and inc=$inc";
							$this->db->DoQuery($nquery);
							if($srow = $this->db->FetchRow()){
								$this->last_r_survey=$srow;
								$lastmd=$md;
								continue;
							} else{	
								$dfstr='';
								$dfvals='';
								$dip=false;
								$fault=false;
								foreach($this->dsurveys as $ds){
									if(($ds['azm']==$azm && $ds['inc']==$inc && $ds['md']==$md) ||
									 ($ds['azm']==$azm && $ds['inc']==$inc) ||
									 ($ds['inc']==$inc && $ds['md']==$md) ||
									 ($ds['azm']==$azm &&  $ds['md']==$md)){
										$dip=$ds['dip'];
										$fault=$ds['fault'];
										$dfstr=',dip,fault';
										$dfvals=",'$dip','$fault'";
										break;
									}
								}
								$nquery = "insert into surveys (azm,inc,md,srcts$dfstr) values ('$azm','$inc','$md','$ts'$dfvals)";
								$this->db->DoQuery($nquery);
								if($svycnt>1){
									$lastmd+=1;
								}
								exec(__DIR__ ."/../sses_gva -d $seldbname",$output);
								exec(__DIR__ ."/../sses_cc -d $seldbname",$output);	
				   				$nfquery = "select * from surveys where azm=$azm" .
		   							" and md=$md" .
		   							" and inc=$inc";
		   					
		   						$this->db->DoQuery($nfquery);
		   						$this->current_r_survey = $this->db->FetchRow();
		   						
								$this->prepare_las_data($lastmd,$md,1,false,$dip,$fault);
								array_push($loaded,$survey);
				   				$this->last_r_survey=$this->current_r_survey;
				   				$lastmd=$md;
							}
   				}
   			}
   		}
   		exec(__DIR__ ."/../sses_gva -d $seldbname",$output);
   		exec(__DIR__ ."/../sses_cc -d $seldbname",$output);	
		$this->db->OpenDb();
		$pterm_query = "update wellinfo set pterm_method = '$pterm_method';";
   		$this->db->DoQuery($pterm_query);
   		$this->db->CloseDb();
   		return $loaded;
   }
   
   function simple_load_last_current(){
   	$queryl = "select * from surveys  where plan=0 order by md desc limit 1";
   	$this->db->DoQuery($queryl);
   	$this->current_r_survey=$this->db->FetchRow();
   	$queryl = "select * from surveys  where plan=0 order by md desc limit 1 offset 1";
   	$this->db->DoQuery($queryl);
   	$this->last_r_survey=$this->db->FetchRow();
   }
   
   function load_next_survey($load=false,$do_cleanup=false){
			if($this->db_name){
					$this->db->OpenDb();
					$surveys = $this->get_survey_from_file();
					if($this->debug){
					    print 'outputing survey data'."\n";
					    print_r($surveys);
					}
					$this->surveys=$surveys;
					$all_surverys_loaded =true;
	   				$new_survey_found = false;
	   				$this->db->CloseDb();
	   				$cleanup_occured = $this->verify_data_integrity($do_cleanup);
	   				$cleanup_message='';
	   				$this->db->OpenDb();
	   				if(!is_numeric($cleanup_occured) && $cleanup_occured){
	   					$cleanup_message .= 'Clean up is needed. If you do not want to clean up ignore this message and continue importing.';
	   					$depth_query = "select max(md) as md from surveys where plan=0";
	   					$this->db->DoQuery($depth_query);
	   					if($srow=$this->db->FetchRow()){
	   						$this->aisd=$srow['md'];
	   					}
	   				}else if(is_numeric($cleanup_occured) && $cleanup_occured){	
	   					$cleanup_message .= 'Clean up has occured due to mismatching md,inc and azm of a previously loaded survey.';
	   				}
	   				$lastmd = $this->aisd;
	   				$svycnt=0;
	   				
	   				$seldbname=$this->db_name;
	   				$queryl = "select * from surveys  where plan=0 order by md asc limit 1";
	   				$this->db->DoQuery($queryl);
	   				$this->last_r_survey=$this->db->FetchRow();
	   				foreach($this->surveys as $survey){
	   					
	   					$md = $survey['md'];
	   					if($md >$this->aisd){	
		   					$svycnt++;
		   					$inc = $survey['inc'];
		   					$azm = $survey['azm'];
		   					//$ts = strtotime($survey['time']);
		   					$nquery = "select * from surveys where azm=$azm" .
		   					" and md=$md" .
		   					" and inc=$inc";
							$this->db->DoQuery($nquery);
							if($srow=$this->db->FetchRow()){
								$this->last_r_survey=$srow;
								$lastmd=$md;
								continue;
							} else{
								if(!$cleanup_occured){
									$dquery = "select * from surveys where md > '$md'  and plan=0 order by md;";
									$this->db->Doquery($dquery);
									$cleanup_surveys=array();
									$prev_md=$md;
									while($row=$this->db->FetchRow()){
										array_push($cleanup_surveys,array('id'=>$row['id'],'sd'=>$prev_md,'ed'=>$row['md'],'raw_srvy'=>$row));
										$prev_md = $row['md'];
									}
									if(count($cleanup_surveys)>0){
										if($do_cleanup){
											$cleanup_occured = $this->cleanup_data($cleanup_surveys);
											if($cleanup_occured){
												$cleanup_message .= 'Clean up has occured due to insertion of a survey amid existing surveys by the data provider.';
											}
										}else{
											$cleanup_occured=true;
											$cleanup_message.='Clean up needed due to insertion of a survey amid existing surveys by the data provider.';
										}
									}
								}
								if($load){
									$nquery = "insert into surveys (azm,inc,md,srcts) values ('$azm','$inc','$md','$ts')";
									$this->db->DoQuery($nquery);
									if($svycnt>1){
										$lastmd+=1;
									}
									exec(__DIR__ ."/../sses_gva -d $seldbname",$output);
									exec(__DIR__ ."/../sses_cc -d $seldbname",$output);	
									$nfquery = "select * from surveys where azm=$azm" .
		   							" and md=$md" .
		   							" and inc=$inc";
		   							$this->db->DoQuery($nfquery);
		   							$this->current_r_survey = $this->db->FetchRow();
									$this->prepare_las_data($lastmd,$md);
								}
								$new_survey_found=true;
								break;
							}
	   					}
	   				}

				   	if($new_survey_found)
					{
				   		return array("next_survey"=>true,"md"=>$md,"inc"=>$inc,"azm"=>$azm,"cleanup_occured"=>$cleanup_occured,"cmes"=>$cleanup_message);
				   	}
					else
					{
				   		return array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"cleanup_occured"=>$cleanup_occured,"cmes"=>$cleanup_message);;
				   	}
	   				
			}else{
   				return array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"msg"=>'Polaris connection error');;
   			}
		}
	}
?>
