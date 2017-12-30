<?php

require_once "PlotObject.class.php";
class AdditionalDataPlot {
	function __construct($request) {
		$this->db_name = $request['seldbname'];
		$this->db=new dbio("$this->db_name");
		$this->db->OpenDb();
		$this->spcount = 1;
		$this->gamma_plot = Array();
		$this->single_plots = Array();
		$this->slides = Array();
		$this->build_slides();
		$this->build();
	}
	
	function get_shared_layout($minvs, $maxvs, $yscale, $addition_yaxis){
		$layout_height = $this->get_layout_height();
		return "{
					height: $layout_height,
					width: 1048,
					margin: {
						l: 60,
						r: 25,
						t: 5,
						b: 5
					},
				  xaxis: {
					autorange: false,
					range: [$minvs, $maxvs],
					nticks: 50,
					rangemode: 'nonnegative',
					fixedrange: true,
					showticklabels: false
				  },
				  yaxis: {
			        autorange: false,
			        fixedrange: true,
			        range: [0, $yscale],
			        nticks: 10,
                    showticklabels: true
	              },
	              $addition_yaxis
				}
				";
	}
	function get_gamma_layout($minvs, $maxvs, $yscale){
		$gamma_plot = $this->get_gamma_add_yaxis();
		return $this->get_shared_layout($minvs,$maxvs,$yscale, $gamma_plot);
	}
	
	function get_single_plot_layout($minvs,$maxvs,$yscale,$cnt){
		$single_plot =$this->get_single_plot_yaxis($cnt);
		return $this->get_shared_layout($minvs,$maxvs,$yscale, $single_plot);
	}
	
	function get_gamma_add_yaxis(){
		$res = "";
		$cnt = 0;
		foreach ($this->gamma_plot as $plot){
			$cnt+=1;
			if($cnt == 1) continue;
			$res .= "
			yaxis$cnt: {
			  autorange: false,
			  range: [$plot->low_scale,$plot->high_scale],
			  overlaying: 'y',
			  side: 'right',
			  fixedrange: true,
			  showticklabels: false
		    },";
		}
		return $res;
	}
	function get_single_plot_yaxis($plot_cnt){
		$res = "";
		$plot = $this->single_plots[$plot_cnt];
		$res .= "
			yaxis2: {
			autorange: false,
			range: [$plot->low_scale,$plot->high_scale],
			overlaying: 'y',
			side: 'right',
			fixedrange: true,
			showticklabels: false
			},";
			

		return $res;
	}
	
	function get_layout_height(){
		$height = 100;
		$graph_count = 1;
		$query = "select count(*) as cnt from edatalogs where enabled=1 and single_plot = 1";
		$this->db->DoQuery($query);
		$this->db->FetchRow();
		$graph_count = $graph_count + $this->db->FetchField('cnt');
		if($graph_count > 1){
			$height = 75;
		}
		return $height;
	}
	function build_slides(){
		$this->db->DoQuery("select * from rotslide where slidestartmd > 0");
		while($this->db->FetchRow()){
			$vs = $this->db->FetchField('slidestartvs');
			$endvs = $this->db->FetchField('slideendvs');
			$slide = new PlotObject(Array(305,305),Array($vs,$endvs));
			$slide->set_line_color("#bfbfff");
			$slide->set_name('wlog');
			$slide->set_showledgend(false);
			$slide->set_fillcolor("#bfbfff");
			$slide->set_filltype("tozeroy");
			array_push($this->slides, $slide);
		}
	}
	function build(){
		$this->db->DoQuery("select max(value) as mval, min(value) as minval from add_data_gamma_fb where value != 9999");
		$this->db->FetchRow();
		
		$this->db2=new dbio("$this->db_name");
		$this->db2->OpenDb();
		$this->db2->DoQuery("select * from add_data_gamma_fb");
		$vss = Array();
		$values = Array();
		$tvds   = Array();
		
		while($this->db2->FetchRow()){
			$value = $this->db2->FetchField('value');
			$tvd   = $this->db2->FetchField('tvd');
			$vs    = $this->db2->FetchField('vs');
			if($value <= -999.25 || $value >= 9999){continue;}
			array_push($vss,$vs);
			array_push($values,$value);
		}
		$edata_log = new PlotObject($values,$vss);
		$edata_log->set_line_color('397648');
		$edata_log->set_name('Gamma');
		$edata_log->set_showledgend(false);
		$edata_log->max_v = $this->db->FetchField('mval');
		$edata_log->min_v = $this->db->FetchField('minval');
		array_push($this->gamma_plot,$edata_log);
		
		$query = "select * from edatalogs where enabled=1 order by single_plot asc";
		$this->db->DoQuery($query);
		$cnt=1;
		while($this->db->FetchRow()){
			$tablename = $this->db->FetchField("tablename");
			$label = $this->db->FetchField("label");
			$color = $this->db->FetchField("color");
			$singleplot = $this->db->FetchField("single_plot");
			$cnt+=1;
			$query2 = "select * from $tablename order by md ASC;";
			$vss = Array();
			$values = Array();
			$tvds   = Array();
			
			$this->db2->DoQuery($query2);
			while($this->db2->FetchRow()){
				$value = $this->db2->FetchField('value');
				$tvd   = $this->db2->FetchField('tvd');
				$vs    = $this->db2->FetchField('vs');
				if($value <= -999.25 || $value >= 9999){continue;}
				array_push($vss,$vs);
				array_push($values,$value);
			}
			$edata_log = new PlotObject($values,$vss);
			$edata_log->set_line_color("#$color");
			$edata_log->set_name($label);
			$edata_log->set_showledgend(false);
			$edata_log->set_scale($this->db->FetchField("scalehi"), $this->db->FetchField("scalelo"));
			$edata_log->set_axis("y$cnt");
			if($singleplot == 1){
			  array_push($this->single_plots,$edata_log);
			  $edata_log->set_axis("y2");
			} else {
			  array_push($this->gamma_plot, $edata_log);
			}
		}
	}
}
?>