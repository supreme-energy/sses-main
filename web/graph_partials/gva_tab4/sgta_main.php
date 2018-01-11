<script type="text/javascript" src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<?
	include_once("classes/graphing/SgtaModelingTab4.class.php");
	
    $db->DoQuery("select * from controllogs limit 1;");
    $db->FetchRow();
    $tot_val = $db->FetchField('tot');
    $bot_val = $db->FetchField('bot');
	$max_x = $scaleright;
	$db->DoQuery("select * from controllogs limit 1");
	$db->FetchRow();
	$controltot = $db->FetchField("tot");
	$controlbot = $db->FetchField("bot");
	
	
	$graph_obj = new SgtaModelingTab4($_REQUEST,$tableid, $plotbias,$secttot, $scaleright, true);

?>
<script>
var layout = {
		  height: 850,
		  margin: {
			l: 60,
			r: 25,
			t: 25,
			b: 25
		  },
		  xaxis: {
			autorange: false,
			range: [0,<?php echo $scaleright?>],
			rangemode: 'nonnegative',
			nticks: 50
		  },
		  yaxis: {
			autorange: false,
            range: [ <?php echo ($graph_obj->cur_depth_max + ($zoom*2))?>, <?php echo ($graph_obj->cur_depth_min - ($zoom*2)) ?>],
			nticks: 50
	      },
	      yaxis2:{
			autorange: false,
			range: [<?php echo ($graph_obj->cur_tvd_max + ($zoom*2))?>, <?php echo ($graph_obj->cur_tvd_min - ($zoom*2)) ?>],
			overlaying: 'y',
			side: 'right'
		  }
		};
var tcl = {
		x: [0, <?php echo $scaleright?>],
		y: [<?php echo $graph_obj->cur_tcl ?>, <?php echo  $graph_obj->cur_tcl ?>],
		yaxis: 'y2',
		name: 'TCL',
		showlegend: false,
		line: {
			color: 'black'
			}
}

viewallds = <?php echo $viewallds ?>


var data = [tcl]
var index = 0
var index_of_selected = index

<?php foreach($graph_obj->addformplots as $log){?>
index+=1
data.push(<?php $log->to_js()?>)
<?php }?>
var first_index = index+1
<?php 
foreach($graph_obj->wellogplots as $log){?>	
  index+=1
  var obj = <?php $log->to_js()?>
  
  data.push(obj)
  if(obj.current_sel){
   index_of_selected = index	
  }
<?php }?>
var last_index = index

data.push(<?php echo $graph_obj->control_log->to_js()?>)


</script>
<div id='well_log_plot' style="<?php if($viewrotds>=1){ ?>transform: rotate(-90deg);<?php }?>"></div>
<script>
//,{scrollZoom: true}
Plotly.newPlot('well_log_plot', data, layout);
var storeLayout = function(){
	console.log('triggered');
}
var graphDiv = document.getElementById('well_log_plot')
var lastscrollEvent = null
var scrollMode = 'fault'

graphDiv.on('plotly_hover', function(data){
	window.onwheel = function(e){
		clearTimeout(lastscrollEvent)
		
		var modifier = 0.1
		
		if(e.shiftKey && !e.altKey){
			modifier *=10
		}
		if(e.altKey && !e.shiftKey){
			modifier /= 2
		}
		if(e.altKey && e.shiftKey){
			modifier *= 100 
		}
		if(e.deltaY > 0){
			modifier*=-1
		}

		if(scrollMode == 'fault'){
		   modifier*=10
		   faultupdown(modifier,true)
		} else {
		   dipupdown(modifier, true )
		}
		return false;
	}
});
graphDiv.on('plotly_relayout', storeLayout);

var sendWellLogFieldUpdate = function(field,value,id){
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '/sses/json.php?path=json/sgta_modeling/update_welllog_field.php&seldbname=<?php echo $seldbname ?>&field='+field+'&value='+value+'&id='+id);
	xhr.send();
}
var sendAppInfoFieldUpdate = function(field,value){
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '/sses/json.php?path=json/sgta_modeling/update_appinfo_field.php&seldbname=<?php echo $seldbname ?>&field='+field+'&value='+value);
	xhr.send();
}

var valueBylookupFieldType = function(inarr, ltype){
	if(ltype=='end'){
		return inarr[inarr.length - 1]
	} else {
		return inarr[0]
	}
}

var updateDataModelingValues = function(){
	var selected = data[index_of_selected]
	document.getElementById('realname_tablename').innerHTML = selected.filename+"( wld_"+selected.tableid+")"
	document.getElementById('sectfault').value = selected.fault
	document.getElementById('sectdip_parent').value = selected.dip
	document.getElementById('scalebias').value = selected.bias
	document.getElementById('scalefactor').value = selected.factor
}
var updateDisplayedValues = function(){
	var selected = data[index_of_selected]
	var fields_to_update = ['md_start_disp', 'md_end_disp', 'tvd_start_disp', 'tvd_end_disp', 'vs_start_disp', 'vs_end_disp']
	for(var i = 0 ; i < fields_to_update.length; i++){
		var field = fields_to_update[i]
		var split_field = field.split('_');
		var new_value = valueBylookupFieldType(selected[split_field[0]],split_field[1])
		document.getElementById(field).innerHTML = new_value
	}
}

var goToDataSetAt    = function(){
	var md = parseInt(document.getElementById("gotodatasetat").value)
	var didmove = false
	var starting_index = index_of_selected
	for(i = first_index; i <= last_index; i++){
		var current = data[i]
		if(current.md[0] <= md && current.md[current.md.length-1] > md){
			new_index = i
			didmove = true
		} 
	}
	if(!didmove){
		if(data[first_index].md[0] >= md){
			new_index = first_index
			didmove = true
		} else if(data[last_index].md[data[last_index].md.length-1] <= md){
			new_index = last_index
			didmove = true
		}		
	}

	if(didmove && starting_index != new_index){
		var move_to = Math.abs(starting_index - new_index)
		if(starting_index > new_index){
				move_to = move_to*-1
		}
		moveDataSetBy(move_to)
		reRangeOnCurrent()
	}
}

var setVisibility = function(index, visible){
	Plotly.restyle('well_log_plot',{
		 visible: visible
		}, index)
}

var viewAll = function(vis,noupdate = false){
	indexes = []
	for(i = first_index; i <= last_index; i++){
		indexes.push(i)
	}
	setVisibility(indexes, vis)
	if(noupdate==false){
	  viewallds = 1
	  sendAppInfoFieldUpdate('viewallds',viewallds)
	}
}

var viewOnlySelected = function(){
	viewAll(false)
	setVisibility(index_of_selected, true)
	viewallds = 0
	sendAppInfoFieldUpdate('viewallds',viewallds)
}

var viewPreviousXMD  = function(){
	viewallds = parseInt(document.getElementById("viewallprevval").value)
	viewAll(false, true)
	setVisibility(index_of_selected, true)
	var indexes = []
	selected = data[index_of_selected]
	for(i = index_of_selected-1; i >= first_index; i--){
		var current = data[i]
		if((selected.md[0] - viewallds) <= current.md[current.md.length-1]){
			indexes.push(i);
		}
	}
	if(indexes.length > 0 ){
	  setVisibility(indexes,true)
	}
	sendAppInfoFieldUpdate('viewallds',viewallds)
}

var showShadow = function(){

}

var moveDataSetBy = function(val){	
	visibility_of_last = true
	if(viewallds == 0){
		visibility_of_last = false
	}
	Plotly.restyle('well_log_plot',{
        visible: visibility_of_last,
		line: {
		    color: '#00008B'
	      }
		}, index_of_selected)
	Plotly.restyle('well_log_plot',{
		visible: true,
		line: {
		    color: '#ff0000'
	      }
		}, index_of_selected+val)	
	index_of_selected +=val	
	if(viewallds > 1){
		viewPreviousXMD()
	}
	updateDisplayedValues()
	updateDataModelingValues()
	sendAppInfoFieldUpdate('tablename',data[index_of_selected].name)
}
var nextDataSet = function(){
	if(index_of_selected+1 > last_index){
		return
	}
	moveDataSetBy(1)
	reRangeOnCurrent()
}
var prevDataSet = function(){
	if(index_of_selected-1 < first_index){
		return
	}
	moveDataSetBy(-1)
	reRangeOnCurrent()
}

var firstDataSet = function(){
	moveDataSetBy(first_index - index_of_selected )
	reRangeOnCurrent()
}

var lastDataSet = function(){
	moveDataSetBy(last_index - index_of_selected)
	reRangeOnCurrent()
}

var reRangeOnCurrent = function(){
	var current = data[index_of_selected]
	var new_yrange = [current.y[current.y.length-1]+(<?php echo $zoom ?>*2),current.y[0]-(<?php echo $zoom ?>*2)]
	Plotly.relayout('well_log_plot',{'yaxis.range': new_yrange})
}

var dipupdown  = function(val, ontimeout=false){
	var selected = data[index_of_selected]
	oldval = parseFloat(document.getElementById('sectdip_parent').value)	
	newval = (oldval+val).toPrecision(3)
	if(newval < -89.9){
		newval = -89.9
	} 
	if(newval > 89.9){
		newval = 89.9
	}
	document.getElementById('sectdip_parent').value = newval
	if(!ontimeout){
		updateDip(document.getElementById('sectdip_parent'))
		sendWellLogFieldUpdate("dip", newval, "wld_"+selected.tableid)
	} else {
		lastscrollEvent = setTimeout(function(){
			var selected = data[index_of_selected]
			updateDip(document.getElementById('sectdip_parent'))
			sendWellLogFieldUpdate("dip", document.getElementById('sectdip_parent').value, "wld_"+selected.tableid)
		}, 250)
	}
}

var faultupdown = function(val, ontimeout=false){
	var selected = data[index_of_selected]
	var oldval = parseFloat(parseFloat(document.getElementById('sectfault').value).toFixed(2))
	var newval = (oldval+val)
	document.getElementById('sectfault').value = Number(newval).toFixed(2)
	if(!ontimeout){
		updateFault(document.getElementById('sectfault'))
		sendWellLogFieldUpdate("fault", newval, "wld_"+selected.tableid)
	} else {
		lastscrollEvent = setTimeout(function(){
			updateFault(document.getElementById('sectfault'))
			var selected = data[index_of_selected]
			sendWellLogFieldUpdate("fault", document.getElementById('sectfault').value, "wld_"+selected.tableid)
		}, 250)
	} 
}

var biasupdown = function(val){
	var selected = data[index_of_selected]
	var oldval = parseFloat(document.getElementById('scalebias').value)
	var newval =  (oldval+val).toPrecision(2)
	document.getElementById('scalebias').value = newval
	updatePlotBias(document.getElementById('scalebias'))
	sendWellLogFieldUpdate("scalebias", newval, "wld_"+selected.tableid)
}

var factorupdown = function(val){
  var selected = data[index_of_selected]
  var oldval = parseFloat(document.getElementById('scalefactor').value)
  var newval =  (oldval+val).toPrecision(2)
  document.getElementById('scalefactor').value = newval
  updateScaleFactor(document.getElementById('scalefactor'))
  sendWellLogFieldUpdate("scalefactor", newval, "wld_"+selected.tableid)
}


var updateScaleFactor = function(infield){
	var newfactor  = parseFloat(infield.value)
	var selected = data[index_of_selected]
	var newx = []
	var oldfactor = parseFloat(selected.factor)
	var bias = parseFloat(selected.bias)
	for(var i = 0; i < selected.x.length; i++){
		var oldval = selected.x[i]
		var newval = (oldval - bias)/oldfactor
		newval *= newfactor
		newval += bias
		newx.push(newval) 
	}
	selected.x = newx
	selected.factor = newfactor
	Plotly.addTraces('well_log_plot' ,selected ,index_of_selected)
	Plotly.deleteTraces('well_log_plot', index_of_selected)
	reRangeOnCurrent()
}
var updatePlotBias = function(infield){
	var newbias  = parseFloat(infield.value)
	var selected = data[index_of_selected]
	var newx = []
	var oldbias = selected.bias
	var addval = newbias - oldbias
	 
	for(var i = 0; i < selected.x.length; i++){
		newx.push(selected.x[i]+addval)
	}
	selected.x = newx
	selected.bias = newbias
	
	Plotly.addTraces('well_log_plot' ,selected ,index_of_selected)
	Plotly.deleteTraces('well_log_plot', index_of_selected)
	reRangeOnCurrent()
}

var updateFault = function(infield){
	var newfault = parseFloat(infield.value)
	var selected = data[index_of_selected]
	var newy = []
	var oldfault = selected.fault
	var addval = newfault-oldfault
	for(var i = 0; i < selected.y.length; i++){
		newy.push(selected.y[i]-addval)
	}
	selected.y = newy
	selected.fault = newfault
	Plotly.addTraces('well_log_plot' ,selected ,index_of_selected)
	Plotly.deleteTraces('well_log_plot', index_of_selected)
	cascadeFaultUpdate(addval)
	reRangeOnCurrent()
}
var calcDepth = function(tvd,dip,vs,lastvs,fault,lasttvd,lastdepth){
	return tvd-(-Math.tan(dip/57.29578)*Math.abs(vs-lastvs))-fault-(lasttvd-lastdepth);
}
var updateDip = function(infield){
	var newdip = parseFloat(infield.value)
	var selected = data[index_of_selected]
	var lastvs = selected.vs[0]
	var lasttvd = selected.tvd[0]
	var lastmd  = selected.md[0]
	var lastdepth = selected.y[0]
	if(index_of_selected != first_index){
	  var previous = data[index_of_selected - 1]
	  lastvs = previous.vs[previous.vs.length-1]
	  lasttvd = previous.tvd[previous.tvd.length-1]
	  lastmd  = previous.md[previous.md.length-1]
	  lastdepth = previous.y[previous.y.length-1]
	}

	newdepths=[]
	for(var i = 0; i < selected.tvd.length; i++){
		vs = selected.vs[i]
		tvd = selected.tvd[i]
		md = selected.md[i]
		depth = calcDepth(tvd,newdip,vs,lastvs,selected.fault,lasttvd,lastdepth)
		newdepths.push(depth)
	}
	selected.y = newdepths
	selected.dip = newdip
	Plotly.addTraces('well_log_plot' ,selected ,index_of_selected)
	Plotly.deleteTraces('well_log_plot', index_of_selected)
	cascadeDipUpdate()
	reRangeOnCurrent()
}

var cascadeDipUpdate = function(){
	var startindex = index_of_selected+1
	var updateIndexes = []
	for(var i = startindex; i <= last_index; i++){
		var selected = data[i]
		var previous = data[i - 1]
		lastvs = previous.vs[previous.vs.length-1]
		lasttvd = previous.tvd[previous.tvd.length-1]
		lastmd  = previous.md[previous.md.length-1]
		lastdepth = previous.y[previous.y.length-1]
		var newdepths = []
		for(var j = 0; j < selected.tvd.length; j++){
			vs = selected.vs[j]
			tvd = selected.tvd[j]
			md = selected.md[j]
			depth = calcDepth(tvd,selected.dip,vs,lastvs,selected.fault,lasttvd,lastdepth)
			newdepths.push(depth)
		}
		selected.y = newdepths
		updateIndexes.push(i);
	}
	Plotly.addTraces('well_log_plot' ,data.slice(startindex,last_index+1), updateIndexes)
	Plotly.deleteTraces('well_log_plot', updateIndexes)
}

var cascadeFaultUpdate = function(addfault){
	var startindex = index_of_selected+1
	var updateIndexes = []
	for(var i = startindex; i <= last_index; i++){
		var selected = data[i]
		var newy = []
		for(var j = 0; j < selected.y.length; j++){
			newy.push(selected.y[j]-addfault)
		}
		selected.y = newy
		updateIndexes.push(i);
	}	
	
	Plotly.addTraces('well_log_plot' ,data.slice(startindex,last_index+1), updateIndexes)
	Plotly.deleteTraces('well_log_plot', updateIndexes)
}
                               

document.addEventListener("DOMContentLoaded", function(event) { 
	if(<?php echo ($viewallds == 1 ? 'true' : 'false')?>){
		viewAll()
	} else if(<?php echo ($viewallds == 0 ? 'true' : 'false') ?>){
		viewOnlySelected()
	} else {
		viewPreviousXMD()
	}
	
})
</script>