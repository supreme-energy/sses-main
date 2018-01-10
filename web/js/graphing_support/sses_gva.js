document.addEventListener("DOMContentLoaded", function(event) { 
  
	var calculateDepth = function(tvd, lasttvd, vs, lastvs, dip, fault, lastdepth){
	 return  tvd-(-Math.tan(dip/57.29578)*Math.abs(vs-lastvs))-fault-(lasttvd-lastdepth)
  }
  
  if(data === undefined){
	console.log("data must be defined");
	
	return
  }
  
  if(index_of_selected === undefined){
	  console.log("index of selected should be defined");
	  return
  }
  var selected = data[index_of_selected]
  var tabledId  = selected.tableId
  var fault     = selected.fault
  var dip       = selected.dip
  
  if(dip < 89.9) dip = -89.9
  if(dip > 89.9) dip = 89.9
  updated_depth = [];
  var row_cnt = 0;
  var table_cnt = 0;
  
  var lastvs  = 0;
  var lasttvd = 0;
  var lastmd  = 0;
  var lastdepth = 0;
  
  for(var i; i < length(selected.depth); i++){
	  updated_depth.push(calculateDepth(tvd, lastTvd, vs, lastVs, dip, fault, lastDepth))
  }
})
