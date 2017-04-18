<?php

require_once ('Config.class.php');
require_once("Survey.class.php");

class WellInfo extends Config {
	function __construct($request) {
		$this->original_request_data=$request;
		$this->query = array (
			"SELECT * FROM wellinfo;",
			"SELECT * from emailinfo;",
			"SELECT * from witsml_details;"
		);
		parent :: __construct($request);
	}
	
	function get_well_witsml_query($uid){
		$result = "<well uid=\"" . $uid . "\">";
		$result .= "<name/>";
		$result .= "</well>";
		return $result;
	}
	
	function get_well_witsml($uid) {

		$result = "<well uid=\"" . $uid . "\">";
		$result .= "<name>" . $this->wellborename . "</name>";

		$result .= "<numGovt>" . $this->rigid . "</numGovt>";
		$result .= "<field>" . $this->field . "</field>";
		$result .= "<country>" . $this->country . "</country>";
		$result .= "<state>" . $this->state . "</state>";
		$result .= "<county>" . $this->county . "</county>";

		$result .= "<operator>" . $this->operator . "</operator>";

		$result .= "<numAPI>" . $this->wellid . "</numAPI>";

		$result .= "<wellheadElevation uom=\"ft\">" . $this->elev_ground . "</wellheadElevation>";
		$result .= "<groundElevation uom=\"ft\">" . $this->elev_ground . "</groundElevation>";

		$result .= "<wellLocation uid=\"\">";
		$result .= "<easting uom=\"ft\">" . $this->survey_easting . "</easting>";
		$result .= "<northing uom=\"ft\">" . $this->survey_northing . "</northing>";
		$result .= "</wellLocation>";
		$result .= "<referencePoint uid=\"SRP1\">";
		$result .= "<name>Survey Location</name>";
		$result .= "<location uid=\"loc-1\">";
		$result .= "<easting uom=\"ft\">" . $this->survey_easting . "</easting>";
		$result .= "<northing uom=\"ft\">" . $this->survey_northing . "</northing>";
		$result .= "</location>";
		$result .= "</referencePoint>";
		$result .= "<referencePoint uid=\"SRP2\">";
		$result .= "<name>Landing Point</name>";
		$result .= "<location uid=\"loc-2\">";
		$result .= "<easting uom=\"ft\">" . $this->landing_easting . "</easting>";
		$result .= "<northing uom=\"ft\">" . $this->landing_northing . "</northing>";
		$result .= "</location>";
		$result .= "</referencePoint>";
		$result .= "<referencePoint uid=\"SRP3\">";
		$result .= "<name>PBHL</name>";
		$result .= "<location uid=\"loc-3\">";
		$result .= "<easting uom=\"ft\">" . $this->pbhl_easting . "</easting>";
		$result .= "<northing uom=\"ft\">" . $this->pbhl_northing . "</northing>";
		$result .= "</location>";
		$result .= "</referencePoint>";
		$result .= "</well>";
		return $result;
	}

	function get_wellbore_witsml($well_uid,$uid) {
		$db = new dbio("$this->db_name");
		$db->OpenDb();
		$db->DoQuery("SELECT * FROM wellplan WHERE hide=0 ORDER BY md ASC");
		$resp_array = array ();
		while ($row = $db->FetchRow()) {
			array_push($resp_array, $row);
		}
		$controls = $resp_array;
		$first = $second = $last = null;
		if (count($controls) > 0) {
			$first = $controls[0];
			if (count($controls) >= 2) {
				$second = $controls[1];
			} else {
				$second = $first;
			}
			$last = $controls[count($controls) - 1];
		}
		
		if ($first && $second && $last) {
			$depth_data = "<md uom=\"ft\">" . $first['md'] . "</md>
						<tvd uom=\"ft\">" . $first['tvd'] . "</tvd>
						<mdKickoff uom=\"ft\">" . $second['md'] . "</mdKickoff>
						<tvdKickoff uom=\"ft\">" . $second['tvd'] . "</tvdKickoff>
						<mdPlanned uom=\"ft\">" . $last['md'] . "</mdPlanned>
						<tvdPlanned uom=\"ft\">" . $last['tvd'] . "</tvdPlanned>
						<mdSubSeaPlanned uom=\"ft\">" . $last['md'] . "</mdSubSeaPlanned>
						<tvdSubSeaPlanned uom=\"ft\">" . $last['tvd'] . "</tvdSubSeaPlanned>";
		} else {
			$depth_data = '';
		}

		$data_set = "<wellbore uidWell=\"$well_uid\" uid=\"$uid\">
					<nameWell>" . $this->wellborename . "</nameWell>
					<name>" . $this->rigid . "</name>
					<numGovt>" . $this->wellid . "</numGovt>
					$depth_data
				</wellbore>";
		return $data_set;
	}

  function get_trajectory_witsml($well_uid,$bore_uid,$uid){
	$this->db->OpenDb();
	$this->db->DoQuery("select * from addforms;");
	$totid =null;
	$botid = null;
	while($this->db->FetchRow()){
		
		if(trim($this->db->FetchField('label'))=='TOT'){
			$totid = $this->db->FetchField('id');
		}
		if(trim($this->db->FetchField('label'))=='BOT'){
			$botid = $this->db->FetchField('id');
		}
	}
	$survey_loader = new Survey($this->original_request_data);	
 	$projections = $survey_loader->get_projs('row');
 	$surveys = $survey_loader->get_surveys('row');
 	$last_survey = $survey_loader->get_last_survey('row');
  	$traj_station_xml='';
 	$prev_proj = $last_survey[0];
 	$prev_survey=null;
 	$max_md=0;
 	$min_md=5000000;
 	$cnt=count($projections);
	foreach($projections as $proj){
		if($proj['md']>$max_md){$max_md=$proj['md'];}
		if($proj['md']<$min_md){$min_md=$proj['md'];}	
		$id = $proj['id'];
		$mddelta = $prev_proj['md']-$proj['md'];
		$tvddelta = $prev_proj['tvd']-$proj['tvd'];
		$northing = $this->survey_northing+$proj['ns'];
		$easting = $this->survey_easting+$proj['ew'];
	 	//station
	 	$traj_station_xml .= "<trajectoryStation uid=\"projection_".$cnt."\">
				<typeTrajStation>marker TVD</typeTrajStation>
				<md uom=\"ft\">".$proj['md']."</md>
				<tvd uom=\"ft\">".$proj['tvd']."</tvd>
				<incl uom=\"dega\">".$proj['inc']."</incl>
				<azi uom=\"dega\">".$proj['azm']."</azi>
				<dispNs uom=\"ft\">".$proj['ns']."</dispNs>
				<dispEw uom=\"ft\">".$proj['ew']."</dispEw>
				<vertSect uom=\"ft\">".$proj['vs']."</vertSect>
				<dls uom=\"dega/ft\">".$proj['dl']."</dls>
				<mdDelta uom=\"ft\">$mddelta</mdDelta>
				<tvdDelta uom=\"ft\">$tvddelta</tvdDelta>
				<location uid=\"prjloc-projection_".$cnt."\">
					<easting uom=\"m\">".$easting."</easting>
					<northing uom=\"m\">".$northing."</northing>
				</location>
			</trajectoryStation>\n";
		//bot formation
		if($totid){
			$query = "select tot from addformsdata where projid=$id and infoid=$totid;";
			$this->db->DoQuery($query);
			$this->db->FetchRow();
			$tot =sprintf("%.2f", $this->db->FetchField("tot")); 
		}
		if($botid){
			$query = "select tot from addformsdata where projid=$id and infoid=$botid;"; 
			$this->db->DoQuery($query);
			$this->db->FetchRow();
			$bot =sprintf("%.2f", $this->db->FetchField("tot")); 
		}
		$traj_station_xml .= "<trajectoryStation uid=\"bot_projection_".$cnt."\">
				<typeTrajStation>formation MD</typeTrajStation>
				<md uom=\"ft\">".$bot."</md>
			</trajectoryStation>\n";
		//tot formation
		$traj_station_xml .= "<trajectoryStation uid=\"tot_projection_".$cnt."\">
				<typeTrajStation>formation MD</typeTrajStation>
				<md uom=\"ft\">".$tot."</md>
			</trajectoryStation>\n";
		//tcl formation
		$traj_station_xml .= "<trajectoryStation uid=\"tcl_projection_".$cnt."\">
				<typeTrajStation>formation MD</typeTrajStation>
				<md uom=\"ft\">".$proj['tot']."</md>
			</trajectoryStation>\n";
		$prev_proj = $proj;	
		$cnt-=1;	
	 }
	$cnt = count($surveys)-1;
 	foreach($surveys as $survey ){
		if($survey['md']>$max_md){$max_md=$survey['md'];}
		if($survey['md']<$min_md){$min_md=$survey['md'];}	
		if($prev_survey){
			$mddelta = $prev_survey['md']-$survey['md'];
			$tvddelta = $prev_survey['tvd']-$survey['tvd'];
		}
		$id = $survey['id'];
		$md = $survey['md'];
		$northing = $this->survey_northing+$survey['ns'];
		$easting = $this->survey_easting+$survey['ew'];
	 	//station
	 	if($survey['plan']){
	 		$station_uid = 'bprj';
	 		$station_type = 'marker TVD';
	 	} else {
	 		$station_uid='actual_'.$cnt;
	 		$station_type='';
	 	}
	 	$traj_station_xml .= "<trajectoryStation uid=\"".$station_uid."\">
				<typeTrajStation>".$station_type."</typeTrajStation>
				<md uom=\"ft\">".$survey['md']."</md>
				<tvd uom=\"ft\">".$survey['tvd']."</tvd>
				<incl uom=\"dega\">".$survey['inc']."</incl>
				<azi uom=\"dega\">".$survey['azm']."</azi>
				<dispNs uom=\"ft\">".$survey['ns']."</dispNs>
				<dispEw uom=\"ft\">".$survey['ew']."</dispEw>
				<vertSect uom=\"ft\">".$survey['vs']."</vertSect>
				<dls uom=\"dega/ft\">".$survey['dl']."</dls>
				";
				if($prev_survey){
				$traj_station_xml.="<mdDelta uom=\"ft\">$mddelta</mdDelta>
				<tvdDelta uom=\"ft\">$tvddelta</tvdDelta>";
				}
				
				$traj_station_xml.="<location uid=\"prjloc-".$station_uid."\">
					<easting uom=\"m\">".$easting."</easting>
					<northing uom=\"m\">".$northing."</northing>
				</location>
			</trajectoryStation>\n";
		//bot formation
		if($totid){
			if($survey['plan']){
				$query = "select tot from addformsdata where md=$md and infoid=$totid;";
			}else{
				$query = "select tot from addformsdata where svyid=$id and infoid=$totid;";
			} 
			$this->db->DoQuery($query);
			$this->db->FetchRow();
			$tot =sprintf("%.2f", $this->db->FetchField("tot"));
		}
		if($botid){
			if($survey['plan']){
				$query = "select tot from addformsdata where md=$md and infoid=$botid;";
			}else{
				$query = "select tot from addformsdata where svyid=$id and infoid=$botid;";
			}
			$this->db->DoQuery($query);
			$this->db->FetchRow();
			$bot =sprintf("%.2f", $this->db->FetchField("tot"));
		}
		$traj_station_xml .= "<trajectoryStation uid=\"bot_".$station_uid."\">
				<typeTrajStation>formation MD</typeTrajStation>
				<md uom=\"ft\">".$bot."</md>
			</trajectoryStation>\n";
		//tot formation
		$traj_station_xml .= "<trajectoryStation uid=\"tot_".$station_uid."\">
				<typeTrajStation>formation MD</typeTrajStation>
				<md uom=\"ft\">".$tot."</md>
			</trajectoryStation>\n";
		//tcl formation
		$traj_station_xml .= "<trajectoryStation uid=\"tcl_".$station_uid."\">
				<typeTrajStation>formation MD</typeTrajStation>
				<md uom=\"ft\">".$survey['tot']."</md>
			</trajectoryStation>\n";
		$prev_survey = $survey;
		$cnt-=1;		 		
 	}
 $data_set = "<trajectory uidWell=\"$well_uid\" uidWellbore=\"$bore_uid\" uid=\"$uid\">
		<nameWellbore>".$this->rigid."</nameWellbore>
	    <nameWell>".$this->wellborename."</nameWell>
	   	<name>SESS Project Ahead</name>		
		<mdMn uom=\"ft\">$max_md</mdMn>
		<mdMx uom=\"ft\">$min_md</mdMx>
		<dispNsVertSectOrig uom=\"ft\">".$last_survey[0]['ns']."</dispNsVertSectOrig>
		<dispEwVertSectOrig uom=\"ft\">".$last_survey[0]['ew']."</dispEwVertSectOrig>
		$traj_station_xml
	</trajectory>";  	
  return $data_set;
  }

}
?>