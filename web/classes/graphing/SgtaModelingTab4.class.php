<?php

class PlotObject {
	function __construct($x,$y,$max_md = 0,$min_md = 0){
		$this->orientation= 'v';		
		$this->x = $y;
		$this->y = $x;
		$this->max_md = $max_md;
		$this->min_md = $min_md;
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
		      .(isset($this->axis) ? "yaxis: '$this->axis',":"" ).
		      "name: '".$this->name."',
		      line: {
			    color: '".$this->line_color."'
		      }}";
	}
}

class SgtaModelingTab4 {
	function __construct($request, $tableid,$plotbias,$tcl, $rightscale) {
		$this->db_name = $request['seldbname'];
		$this->db=new dbio("$this->db_name");
		$this->db->OpenDb();
		$this->cur_depth_max = 0;
		$this->cur_depth_min = 0;
		$this->cur_tvd_max = 0;
		$this->cur_tvd_min = 0;
		$this->cur_tcl = 0;
		$this->plotbias = $plotbias;
		$this->secttot = $tcl;
		$this->rightscale = $rightscale;
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
		$this->cur_tcl = $this->db->FetchField('tot');
		
		$this->db->DoQuery("select max(depth) as maxdepth, min(depth) as mindepth, max(tvd) as maxtvd, min(tvd) as mintvd from wld_$tableid");
		$this->db->FetchRow();

		$this->cur_depth_max = $this->db->FetchField('maxdepth');
		$this->cur_depth_min = $this->db->FetchField('mindepth');
		$this->cur_tvd_max = $this->db->FetchField('maxtvd');
		$this->cur_tvd_min = $this->db->FetchField('mintvd');

		$this->db->DoQuery("Select * from wld_$tableid order by md;");
		
		while($this->db->FetchRow()){
			array_push($cur_x_plot, $this->db->FetchField("depth"));
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
				array_push($x_plot, $this->db2->FetchField("depth"));
				$val = $this->db2->FetchField("value");
				$val *= $this->db->FetchField('scalefactor');
				$val += $this->db->FetchField('scalebias') + $this->plotbias;
				array_push($y_plot, $val);
			}
			
			$log = new PlotObject($x_plot,$y_plot,$this->db->FetchField('endmd'),$this->db->FetchField('startmd'));
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
			$x = $this->cur_tcl+$this->db2->FetchField("thickness");
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
