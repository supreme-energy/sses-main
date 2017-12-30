<?php

require_once "PlotObject.class.php";
class WellBorePlot {
	function __construct($request) {
		$this->db_name = $request['seldbname'];
		$this->db=new dbio("$this->db_name");
		$this->db->OpenDb();
		$this->build();
		$this->formations = Array();
		$this->annotations = Array();
		$this->build_formations();
		
		$this->formation_projections = Array();
	}
	
	function get_layout_height(){
		$height = 698;
		$sub_graph_height = 100;
		$sub_graph_count = 1;
		$query = "select count(*) as cnt from edatalogs where enabled=1 and single_plot = 1";
		$this->db->DoQuery($query);
		$this->db->FetchRow();
		$sub_graph_count = $sub_graph_count + $this->db->FetchField('cnt');
		if($sub_graph_count > 1){
			$sub_graph_height = 75;
		}
		return $height - ($sub_graph_height*$sub_graph_count);
	}
	function build_projections(){
		$proj_depths = Array();
		$proj_vs = Array();
		$proj_tcl_depths = Array($this->tcl->y[count($this->tcl->y)-1]);
		$this->db->DoQuery("SELECT * FROM projections ORDER BY md");
		while($this->db->FetchRow()){
			array_push($proj_tcl_depths, $this->db->FetchField('tot'));
			array_push($proj_depths, $this->db->FetchField('tvd'));
			array_push($proj_vs, $this->db->FetchField('vs'));
		}
		$this->proj_tcl = new PlotObject($proj_tcl_depths,array_merge(Array($this->tcl->x[count($this->tcl->x)-1]),$proj_vs));
		$this->proj_tcl->set_line_color('#D05050');
		$this->proj_tcl->set_name('TCL PROJ');
		$this->proj_tcl->set_showledgend(false);
		$this->projections = new PlotObject($proj_depths,$proj_vs);
		$this->projections->set_line_color('red');
		$this->projections->set_name('Projections');
		$this->projections->set_showledgend(true);
		$this->projections->set_markers('circle-open','markers',8,'red');
	}
	
	function build(){
		$this->db->DoQuery("SELECT count(*) as cnt FROM surveys");
		$this->db->FetchRow();
		$surveycnt = $this->db->FetchField('cnt');
		$this->db->DoQuery("SELECT * FROM surveys ORDER BY md");
		
		$tcl_depths = Array();
		$survey_depths = Array();
		$survey_depths_p1 = Array();
		$survey_depths_m1 = Array(); 
		$vs = Array();
		$bit_tvd = 0;
		$bit_vs = 0;
		$final_survey_tvd = 0;
		$final_survey_vs  = 0;
		$curcnt = 0;
		while($this->db->FetchRow()){			
			if($this->db->FetchField('plan')==0){		
				array_push($tcl_depths, $this->db->FetchField('tot'));
				array_push($survey_depths_p1, $this->db->FetchField('tvd')+1);
				array_push($survey_depths_m1, $this->db->FetchField('tvd')-1);
				array_push($vs, $this->db->FetchField('vs'));
				if($curcnt >= $surveycnt-2){
					$final_survey_tvd = $this->db->FetchField('tvd');
					$final_survey_vs  = $this->db->FetchField('vs');
				} else {
					array_push($survey_depths, $this->db->FetchField('tvd'));
				}
			} else {
				$bit_tvd = $this->db->FetchField('tvd');
				$bit_vs  = $this->db->FetchField('vs');
			}
			$curcnt+=1;
		}

		
		$this->tcl = new PlotObject($tcl_depths,$vs);
		$this->tcl->set_line_color('black');
		$this->tcl->set_name('Control');
		$this->tcl->set_showledgend(false);
		
		$this->surveys_top = new PlotObject($survey_depths_p1,$vs);
		$this->surveys_top->set_line_color('black');
		$this->surveys_top->set_showledgend(false);
		$this->surveys_top->set_name('');
		
		$this->surveys_bot = new PlotObject($survey_depths_m1,$vs);
		$this->surveys_bot->set_line_color('black');
		$this->surveys_bot->set_showledgend(true);
		$this->surveys_bot->set_name('Wellbore');
		$this->surveys_bot->set_fillcolor("#EBEDEF");
		
		$this->surveys = new PlotObject($survey_depths,$vs);
		$this->surveys->set_line_color('blue');
		$this->surveys->set_name('Surveys');
		$this->surveys->set_showledgend(true);
		$this->surveys->set_markers('star-open-dot','markers',4,'blue');
		
		$this->final_survey = New PlotObject(Array($final_survey_tvd), Array($final_survey_vs ));
		$this->final_survey->set_line_color('red');
		$this->final_survey->set_name('BitPrj');
		$this->final_survey->set_showledgend(true);
		$this->final_survey->set_markers('star-open-dot','markers',4,'red');
		
		$this->bit = New PlotObject(Array($bit_tvd ), Array($bit_vs));
		$this->bit->set_line_color('red');
		$this->bit->set_name('BitPrj');
		$this->bit->set_showledgend(true);
		$this->bit->set_markers('circle-open','markers',8,'red');
		$this->build_wellplan();
		$this->build_projections();
	}

	function build_wellplan(){
		$this->db->DoQuery("select * from wellinfo");
		$this->db->FetchRow();
		$color = $this->db->FetchField("colorwp");
		$this->db->DoQuery("select * from wellplan order by md");
		$wellplan_tvd = Array();
		$wellplan_vs  = Array();
		while($this->db->FetchRow()){
			array_push($wellplan_tvd, $this->db->FetchField('tvd'));
			array_push($wellplan_vs, $this->db->FetchField('vs'));
		}
		$this->wellplan = new PlotObject($wellplan_tvd,$wellplan_vs );
		$this->wellplan->set_line_color($color);
		$this->wellplan->set_name('Wellplan');
		$this->wellplan->set_showledgend(true);
	}
	
	function build_projection_formations(){
		$this->db->DoQuery("select * from addforms order by thickness desc");
		$cnt = 0;
		$bottom_y = Array();
		foreach($this->proj_tcl->y as $whatevs){
			array_push($bottom_y, $whatevs+30000);
		}
		$bottom_out = new PlotObject($bottom_y,$this->tcl->x);
		$bottom_out->set_line_color("black");
		$bottom_out->set_name("bottom");
		$bottom_out->set_showledgend(false);
		array_push($this->formations, $bottom_out);
		while($this->db->FetchRow()){
			$thickness = $this->db->FetchField("thickness");
			$y = Array();
			foreach($this->proj_tcl->y as $tcl_y){
				array_push($y, ($tcl_y+$thickness));
			}
				
			$formation = new PlotObject($y,$this->proj_tcl->x);
			$formation->set_line_color("red");
			$formation->set_name($this->db->FetchField("label"));
			$formation->set_showledgend(false);
			if($this->db->FetchField("bg_color")!=""){
				list($r, $g, $b) = sscanf("#".$this->db->FetchField("bg_color"),"#%02x%02x%02x");
				$formation->set_fillcolor("rgba($r,$g,$b,0.4)");
			}
			array_push($this->formations, $formation);
			$cnt++;
		}		
	}
	
	function build_formations(){				
		
		$this->db->DoQuery("select * from addforms order by thickness desc");
		$cnt = 0;
		$bottom_y = Array();
		foreach($this->tcl->y as $whatevs){
			array_push($bottom_y, $whatevs+30000);
		}
		$bottom_out = new PlotObject($bottom_y,$this->tcl->x);
		$bottom_out->set_line_color("black");
		$bottom_out->set_name("bottom");
		$bottom_out->set_showledgend(false);
		array_push($this->formations, $bottom_out);
		while($this->db->FetchRow()){
			$thickness = $this->db->FetchField("thickness");
			$y = Array();
			foreach($this->tcl->y as $tcl_y){
				array_push($y, ($tcl_y+$thickness));				
			}
			
			$formation = new PlotObject($y,$this->tcl->x);
			$formation->set_line_color("#".$this->db->FetchField('color'));
			$formation->set_name($this->db->FetchField("label"));
			$formation->set_showledgend(true);
			if($this->db->FetchField("bg_color")!=""){
			  list($r, $g, $b) = sscanf("#".$this->db->FetchField("bg_color"),"#%02x%02x%02x");
			  $formation->set_fillcolor("rgba($r,$g,$b,0.4)");
			}
			array_push($this->formations, $formation);
			$cnt++;
		}
		$this->build_projection_formations();
	}

}
?>