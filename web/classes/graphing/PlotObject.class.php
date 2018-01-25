<?php 
class PlotObject {
	function __construct($x,$y,$max_md = 0,$min_md = 0){
		$this->orientation= 'v';		
		$this->x = $y;
		$this->y = $x;
		$this->ids = Array();
		$this->md  = Array();
		$this->tvd = Array();
		$this->vs  = Array();
		$this->detph = Array();
		$this->tcl   = 0;
		$this->tableid  = '';
		$this->filename ='';
		$this->fault = 0;
		$this->dip = 0;
		$this->bias = 0;
		$this->factor = 0;
		$this->max_md = $max_md;
		$this->min_md = $min_md;
		$this->max_v = 0;
		$this->min_v = 0;
		$this->max_tvd = 0;
		$this->min_tvd = 0;
		$this->fillcolor = false;
		$this->custom_marker = false;
		$this->filltype = 'tonexty';
		$this->current_sel = false;
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
	
	function set_fillcolor($color){
		$this->fillcolor = $color;
	}
	
	function set_fillstyle($style){
		$this->fillstyle = $style;
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
	
	function set_scale($high, $low){
		$this->high_scale = $high;
		$this->low_scale  = $low;
	}
	
	function set_markers($symbol, $mode, $size=6,$color='blue'){
		$this->custom_marker = true;
		$this->marker_symbol = $symbol;
		$this->marker_mode = $mode;
		$this->marker_size = $size;
		$this->marker_color = $color;
	}
	function marker_to_js(){
		if($this->custom_marker){
			return "
			mode: '$this->marker_mode',	
			marker: {
				symbol: '$this->marker_symbol',
			    size: $this->marker_size,
			    color: '$this->marker_color'
			}";
		} else {
			return "
					mode: 'lines',
					";
		}
	}
	
	function js_axis(){
		echo "";
	}
	
	function set_filltype($filltype){
		$this->filltype = $filltype;
	}
	
	function to_js(){
		echo "{
			  y: ".'[' . implode(',', $this->y) . ']'.",
		      x: ".'[' . implode(',', $this->x) . ']'.",
		      ". (count($this->ids)>0 ? ("ids: ".'['.implode(',', $this->ids) .']'.",") : '')
		      ."tvd:".'['.implode(',', $this->tvd) .']'.",
		      vs: ".'['.implode(',', $this->vs) .']'.",
		      md: ".'['.implode(',', $this->md) .']'.",
		      tcl: ".$this->tcl.",
		      tableid: '".$this->tableid."',
		      type: 'scatter',
		      hoverinfo: 'none',
		      min_tvd: $this->min_tvd,
		      max_tvd: $this->max_tvd,
		      fault: $this->fault,
		      dip: $this->dip,
		      bias: $this->bias,
		      factor: $this->factor,
		      filename: '".$this->filename."',
		      current_sel: ".($this->current_sel ? "true" : "false").",
		      showlegend: ". ($this->show_ledgend ? "true" : "false") .","
		      .(isset($this->axis) ? "yaxis: '$this->axis',":"" ).
		      "name: '".$this->name."',
		      ". ($this->fillcolor ? "fill: '$this->filltype', fillcolor: '$this->fillcolor'," : "") ."		
		      line: {
			    color: '".$this->line_color."'
		      },
			  ".$this->marker_to_js()."}";
	}
}
?>