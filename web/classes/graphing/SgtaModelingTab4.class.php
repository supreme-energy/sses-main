<?php

require_once "PlotObject.class.php";

class SgtaModelingTab4 {
	function __construct($request, $tableid,$plotbias,$tcl, $rightscale, $no_ledgend = false) {
		$this->db_name = $request['seldbname'];
		$this->db=new dbio("$this->db_name");
		$this->no_ledgends = !$no_ledgend;
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
		$this->control_log->set_showledgend(false && $this->no_ledgends);
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
		$this->current_dataset->set_showledgend(false && $this->no_ledgends);
	}
	
	function prepare_visible_data_set_plot($tableid){
		$this->db2=new dbio("$this->db_name");
		$this->db2->OpenDb();
		$this->db->DoQuery("select * from welllogs order by startmd");
		while($this->db->FetchRow()){
			$tablename = $this->db->FetchField('tablename');
			
			$this->db2->DoQuery("select * from $tablename order by md");
			$x_plot = Array();
			$y_plot = Array();
			$depth = Array();
			$vs = Array();
			$tvd = Array();
			$md  = Array();
			$ids = Array();
			while($this->db2->FetchRow()){
				$d = $this->db2->FetchField("depth");
				array_push($x_plot, $d);
				$val = $this->db2->FetchField("value");
				$val *= $this->db->FetchField('scalefactor');
				$val += $this->db->FetchField('scalebias') + $this->plotbias;
				array_push($y_plot, $val);
				array_push($depth, $this->db2->FetchField("depth"));
				array_push($vs, $this->db2->FetchField("vs")); 
				array_push($md, $this->db2->FetchField("md"));
				array_push($tvd, $this->db2->FetchField("tvd"));
				array_push($ids, $this->db2->FetchField("id"));
			}
			
			$log = new PlotObject($x_plot,$y_plot,$this->db->FetchField('endmd'),$this->db->FetchField('startmd'));
			$log->ids = $ids;
			$log->tvd = $tvd;
			$log->md  = $md;
			$log->vs = $vs;
			$log->tcl = $this->db->FetchField('tot');
			$log->tableid = $this->db->FetchField('id');
			$log->filename = $this->db->FetchField('realname');
			$log->depth = $depth;
			$log->fault = $this->db->FetchField('fault');
			$log->dip   = $this->db->FetchField('dip');
			$log->bias  = $this->db->FetchField('scalebias');
			$log->factor = $this->db->FetchField('scalefactor');
			if("wld_$tableid" == $tablename){
			  $log->set_line_color( '#ff0000');
			  $log->current_sel = true;
			} else {
			  $log->set_line_color('#00008B');
			}
			$log->min_tvd = $this->db->FetchField('starttvd');
			$log->max_tvd = $this->db->FetchField('endtvd');
			$log->set_name($tablename);
			$log->set_showledgend(false && $this->no_ledgends);
			array_push($this->wellogplots,$log);
		}
		$this->db2->CloseDb();
	}
	
	function prepare_addforms_plot(){
		$this->db2=new dbio("$this->db_name");
		$this->db2->OpenDb();
		$this->db->DoQuery("select * from addforms order by id asc");
		$this->formation_thickness = Array();
		while($this->db->FetchRow()){
			$infoid = $this->db->FetchField("id");
			$this->db2->DoQuery("select thickness from addformsdata where infoid=$infoid order by md asc");
			$this->db2->FetchRow();
			array_push($this->formation_thickness,$this->db2->FetchField("thickness"));
			$x = $this->cur_tcl+$this->db2->FetchField("thickness");
			$log = new PlotObject(Array($x,$x),Array(0,300));
			$log->set_line_color($this->db->FetchField("color"));
			$log->set_name($this->db->FetchField("label"));
			$log->set_showledgend(true && $this->no_ledgends);
			$log->set_axis('y2');
			array_push($this->addformplots,$log);
		}
		$this->db2->CloseDb();
	}
	
	
}
?>
