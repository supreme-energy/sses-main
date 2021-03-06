  <?php 
  include ("../../api_header.php");
  function cloudSurveyCheck($do_load, $do_cleanup){
      global $db, $seldbname;
      require_once("../../../classes/WitsmlData.class.php");  
      include("../../../readwellinfo.inc.php");
      $db->CloseDb();
      if($autorc_type=='rigminder'){
          require_once('../../../classes/RigMinderConnection.php');
          header('Content-type: application/json');
          $obj= new RigMinderConnection($_REQUEST);
          $next = $obj->load_next_survey($do_load);
          return json_encode($next);
      }elseif($autorc_type=='polaris' || $autorc_type=='digidrill' || $autorc_type=='welldata'){
          require_once('../../../classes/PolarisConnection.class.php');
          header('Content-type: application/json');
          $obj= new PolarisConnection($_REQUEST);
          $obj->autorc_type=$autorc_type;
          $db->OpenDB();
          $query = "select * from witsml_details";
          $db->DoQuery($query);
          $row = $db->fetchRow();
          if(!$row['wellid'] || !$row['boreid'] || !$row['logid']){
              $next = array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"msg"=>'Connection not configured. Please configure the connection selecting a well, a well bore and a log.');;
          } else {
              $obj->uidWell=$row['wellid'];
              $obj->uidWellBore=$row['boreid'];
              //$obj->logid=$row['logid'];
              $next = $obj->load_next_survey($do_load,$do_cleanup);
          }
          return json_encode($next);
      }elseif($autorc_type=='lasfile'){
          require_once('../../../classes/LasFileConnection.class.php');
          header('Content-type: application/json');
          $filename="/tmp/custom_import_$seldbname.las";
          $f2 = "/tmp/survey_import_$seldbname.las";
          $infile=file_exists ("$filename");
          $infile2=file_exists ("$f2");
          if(!$infile || !$infile2){              
              $next = array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"msg"=>'File not uploaded');
          } else {              
              $obj = new LasFileConnection($_REQUEST);
              $next = $obj->load_next_survey($do_load,$do_cleanup);
              if($next['add_reload']){
                  $obj->import_add_data();
              }
          }
          return json_encode($next);
      }
  }
  ?>