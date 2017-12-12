<?php

class PlotObject {
	function __construct($x,$y){
		$this->orientation= 'v';		
		$this->x = $y;
		$this->y = $x;
	}
	
	function set_line_color($lc){
		$this->line_color = $lc;
	}
		
	function set_showledgend($sl){
		$this->show_ledgend = $sl;
	}
	
	function set_name($name){
		$this->name = $name;
	}
	
	function set_orientation($o){
		if($o != 'v' || $o !='h'){
			return;
		}
		if($this->orientation!=$o){
		  $this->orientation = $o;
		  $temp_x = $this->x;
		  $temp_y = $this->y;
		  $this->x = $temp_y;
		  $this->y = $temp_x;
		}
	}
	
	function set_axis($axis){
		$this->axis = $axis;
	}
	function to_js(){
		echo "{
			  y: ".'[' . implode(',', $this->y) . ']'.",
		      x: ".'[' . implode(',', $this->x) . ']'.",
		      type: 'scatter',
		      showlegend: ". ($this->show_ledgend ? "true" : "false") .","
		      .(isset($this->axis) ? "axis: '$this->axis',":"" ).
		      "name: '".$this->name."',
		      line: {
			    color: '".$this->line_color."'
		      }}";
	}
}

class SgtaModelingTab4 {
	function __construct($request, $tableid,$plotbias,$tcl) {
		$this->db_name = $request['seldbname'];
		$this->db=new dbio("$this->db_name");
		$this->db->OpenDb();
		$this->plotbias = $plotbias;
		$this->secttot = $tcl;
		$this->prepare_control_log_plot();
		$this->prepare_current_data_plot($tableid);
		$this->wellogplots = Array();
		$this->addformplots = Array();
		$this->prepare_visible_data_set_plot($tableid);
		$this->prepare_addforms_plot();
	}
	
	function prepare_control_log_plot(){
		$control_log_x = Array();
		$control_log_y = Array();
		$this->db->DoQuery("select * from controllogs limit 1");
		$this->db->FetchRow();
		$tablename = $this->db->FetchField('tablename');
		$this->db->DoQuery("select * from $tablename  order by md");
		while($this->db->FetchRow()){
			array_push($control_log_x, $this->db->FetchField("md"));
			array_push($control_log_y, $this->db->FetchField("value"));
		}
		$this->control_log = new PlotObject($control_log_x,$control_log_y);
		$this->control_log->set_line_color('#707070');
		$this->control_log->set_name('Control');
		$this->control_log->set_showledgend(false);
	}
	
	function prepare_current_data_plot($tableid){
		$cur_x_plot = Array();
		$cur_y_plot = Array();
		$this->db->DoQuery("select * from welllogs where id = $tableid");
		$table_info  = $this->db->FetchRow();
		$this->db->DoQuery("Select * from wld_$tableid order by md;");
		while($this->db->FetchRow()){
			array_push($cur_x_plot, $this->db->FetchField("md"));
			$val = $this->db->FetchField("value");
			$val *= $table_info['scalefactor'];
			$val += $table_info['scalebias'] + $this->plotbias;
			array_push($cur_y_plot, $val);
		}
		$this->current_dataset = new PlotObject($cur_x_plot,$cur_y_plot);
		$this->current_dataset->set_line_color( '#ff0000');
		$this->current_dataset->set_name('Current');
		$this->current_dataset->set_showledgend(false);
	}
	
	function prepare_visible_data_set_plot($tableid){
		$this->db2=new dbio("$this->db_name");
		$this->db2->OpenDb();
		$this->db->DoQuery("select * from welllogs where tablename != 'wld_$tableid'");
		while($this->db->FetchRow()){
			$tablename = $this->db->FetchField('tablename');
			$this->db2->DoQuery("select * from $tablename order by md");
			$x_plot = Array();
			$y_plot = Array();
			while($this->db2->FetchRow()){
				array_push($x_plot, $this->db2->FetchField("md"));
				array_push($y_plot, $this->db2->FetchField("value"));
			}
			$log = new PlotObject($x_plot,$y_plot);
			$log->set_line_color('#00008B');
			$log->set_name($tablename);
			$log->set_showledgend(false);
			array_push($this->wellogplots,$log);
		}
		$this->db2->CloseDb();
	}
	
	function prepare_addforms_plot(){
		$this->db2=new dbio("$this->db_name");
		$this->db2->OpenDb();
		$this->db->DoQuery("select * from addforms order by id asc");
		while($this->db->FetchRow()){
			$infoid = $this->db->FetchField("id");
			$this->db2->DoQuery("select thickness from addformsdata where infoid=$infoid order by md asc");
			$this->db2->FetchRow();
			$x = $this->secttot+$this->db2->FetchField("thickness");
			$log = new PlotObject(Array($x,$x),Array(0,300));
			$log->set_line_color($this->db->FetchField("color"));
			$log->set_name($this->db->FetchField("label"));
			$log->set_showledgend(true);
			$log->set_axis('y2');
			array_push($this->addformplots,$log);
		}
		$this->db2->CloseDb();
	}
	
	
}
?>
