var shadowFirstDraw = true
var initializeShadow = function(){
	if(isShadowOn){
		shadowPiecesVal = parseInt(document.getElementById('shadow_view_cnt').value)
		shadowSet(true, shadowPiecesVal - 1, true)
	}
}

var showShadow = function(inputel){
	shadowPiecesVal = parseInt(inputel.value)
	sendAppInfoFieldUpdate('viewdspcnt', inputel.value)
	if(shadowPiecesVal>=1){
		document.getElementById('dropshadow_container').style='width: 270;'
		shadowSet(true, shadowPiecesVal - 1, false)
	} else {
		document.getElementById('dropshadow_container').style='width: 270; display:none'
		shadowSet(false,0, false)
	}
}

var shadowSet = function(onoff, numberOfTraces, initializing){
	isShadowOn = onoff
	var adjustedTraceIndexes = []
	if(onoff==false || shadowTraces.length > 0){
		
		for(i=shadow_start_index;i <= shadow_end_index; i++){
			adjustedTraceIndexes.push(i)
		}
		try{
			Plotly.deleteTraces('well_log_plot',adjustedTraceIndexes)
			shadowFirstDraw = true
			if(onoff==false){
				return;
			}
		}catch(e){}
	}
	
	shadowTraces = []
	var adjustedStartIndex = index_of_selected - numberOfTraces
	shadow_end_index = shadow_start_index+numberOfTraces
	if(adjustedStartIndex < first_index){
		adjustedStartIndex = first_index
	}
	var traceDipAvg = 0
	for(var i=adjustedStartIndex; i <= index_of_selected; i++){
		var newTrace = JSON.parse(JSON.stringify(data[i]));
		
		newTrace.line.color = '#EA6D24'	
		shadowTraces.push(newTrace)
		traceDipAvg += parseFloat(newTrace.dip)

	}
	traceDipAvg = traceDipAvg/shadowTraces.length
	console.log(traceDipAvg)
	Plotly.addTraces('well_log_plot', shadowTraces)
	if(!initializing){
		var firstTrace = shadowTraces[0]
		document.getElementById('shadow_bias').value = firstTrace.bias-30
		document.getElementById('shadow_scale').value = firstTrace.factor
		document.getElementById('shadow_fault').value = firstTrace.fault		
		document.getElementById('shadow_dip').value = traceDipAvg
	}

	shadowBias(document.getElementById('shadow_bias').value)
	shadowScale(document.getElementById('shadow_scale').value)
	shadowDip(document.getElementById('shadow_dip').value)
	shadowFault(document.getElementById('shadow_fault').value)
	//firstTrace = shadowTraces[0]
	//document.getElementById('shadow_fault').value = firstTrace.fault
}

var shadowUpdateIndices = function(){
	var updateIndices = []
	for(var i = shadow_start_index; i <= shadow_end_index; i++){
		updateIndices.push(i)
	}
	return updateIndices
}

var shadowBiasUpDown = function(adjustment){
	var element = document.getElementById('shadow_bias')
	var val = parseFloat(element.value)
	var newval = val + adjustment
	element.value = newval
	shadowBias(newval)
}

var shadowScaleUpDown = function(adjustment){
	var element = document.getElementById('shadow_scale')
	var val = parseFloat(element.value)
	var newval = val + adjustment
	element.value = newval
	shadowScale(newval)
}

var shadowBias  = function(bias){
	var newbias  = parseFloat(bias)
	var updateIndices = shadowUpdateIndices()
	var newxs = []
	for(var i = 0; i < shadowTraces.length; i++){
	  var trace = shadowTraces[i]
	  var newx = []
	  var oldbias = trace.bias
	  var addval = newbias - oldbias
	  for(var j = 0; j < trace.x.length; j++){
		newx.push(trace.x[j]+addval)
	  }
	  trace.x = newx
	  trace.bias = newbias
	  newxs.push(newx)	
	}

	Plotly.restyle('well_log_plot' ,{x: newxs}, updateIndices)
	sendAppInfoFieldUpdate("dscache_bias", bias)
}

var shadowScale = function(scale){
	var newfactor  = parseFloat(scale)
	var updateIndices = shadowUpdateIndices()
	var newxs = []
	for(var i = 0; i < shadowTraces.length; i++){
	 	var trace = shadowTraces[i]
		var newx = []
		var oldfactor = parseFloat(trace.factor)
		var bias = parseFloat(trace.bias)
		for(var j = 0; j < trace.x.length; j++){
			var oldval = trace.x[j]
			var newval = (oldval - bias)/oldfactor
			newval *= newfactor
			newval += bias
			newx.push(newval)
		} 
		trace.x = newx
		trace.factor = newfactor
		newxs.push(newx)
	}
	
	Plotly.restyle('well_log_plot' ,{x: newxs}, updateIndices)	
	sendAppInfoFieldUpdate("dscache_scale", scale)
}

var shadowFaultUpDown = function(adjustment){
	var element = document.getElementById('shadow_fault')
	var val = parseFloat(parseFloat(element.value).toFixed(2))
	var newval = val + adjustment
	element.value = newval
	shadowFault(newval)
}

var shadowFault = function(fault){
	var newfault = parseFloat(fault)
	var newys = []
	var updateIndices = shadowUpdateIndices()
	var addval = 0
	var indexOfLastUnshadowedPiece = index_of_selected - shadowPiecesVal
	console.log(indexOfLastUnshadowedPiece)
	if(indexOfLastUnshadowedPiece < first_index){
		console.log('shadowing from start')
	}
	var cumulative_fault =0
	for(var i = 0; i < shadowTraces.length; i++){
		var trace = shadowTraces[i]
		var oldfault = trace.fault
		if(i==0){
			if(i != (shadowTraces.length-1) && shadowFirstDraw){
				addval = newfault * -1				
			} else {
				addval = newfault-oldfault
			}
		} else if(i != (shadowTraces.length-1) && shadowFirstDraw ){
			cumulative_fault -= trace.fault
			addval = (newfault + trace.fault)*-1
		}
		
		var newy = []
		for(var j = 0; j < trace.y.length; j++){
			newy.push(trace.y[j]-addval)
		}
		trace.y = newy
		if(i==0){
			trace.fault = newfault	
		} 	
		newys.push(newy)		
	}
	shadowFirstDraw = false
	Plotly.restyle('well_log_plot' ,{y: newys}, updateIndices)
	sendAppInfoFieldUpdate("dscache_fault", fault)
}

var shadowDipUpDown = function(adjustment){
	var element = document.getElementById('shadow_dip')
	var val = parseFloat(parseFloat(element.value).toFixed(2))
	var newval = val + adjustment
	element.value = newval
	shadowDip(newval)
}

var shadowDip = function(dip){
	var shadowPiecesVal = parseInt(document.getElementById('shadow_view_cnt').value)
	var indexOfLastUnshadowedPiece = index_of_selected - shadowPiecesVal
	
	var selected = data[indexOfLastUnshadowedPiece]
	var previous = selected
	var lastvs = selected.vs[0]
	var lasttvd = selected.tvd[0]
	var lastmd  = selected.md[0]
	var lastdepth = selected.y[0]

	
	if(indexOfLastUnshadowedPiece >= first_index){
 	  previous = data[indexOfLastUnshadowedPiece]
	  lastvs = previous.vs[previous.vs.length-1]
	  lasttvd = previous.tvd[previous.tvd.length-1]
	  lastmd  = previous.md[previous.md.length-1]
	  lastdepth = previous.y[previous.y.length-1]
	} else {
		selected = data[first_index]
		lastvs = selected.vs[0]
		lasttvd = selected.tvd[0]
		lastmd  = selected.md[0]
		lastdepth = selected.y[0]
		if(!shadowFirstDraw || shadowPiecesVal==1){
			lastdepth += selected.fault
		}
	}
	var newys = []
	
	for(var i = 0; i < shadowTraces.length; i++){
		var newdepths=[]
		selected = shadowTraces[i]
		console.log(selected)
		selected.dip = dip
		if(i!=0){
			lastvs = previous.vs[previous.vs.length-1]
			lasttvd = previous.tvd[previous.tvd.length-1]
			lastmd  = previous.md[previous.md.length-1]
			lastdepth = previous.y[previous.y.length-1]
			if(!shadowFirstDraw){
				lastdepth -= selected.fault
			}
		}
		var newdepths = []
		for(var j = 0; j < selected.tvd.length; j++){
			vs = selected.vs[j]
			tvd = selected.tvd[j]
			md = selected.md[j]
			depth = calcDepth(tvd,selected.dip,vs,lastvs,selected.fault,lasttvd,lastdepth)
			
			newdepths.push(depth)
		}
		selected.y = newdepths
		previous = selected
		newys.push(newdepths)
	}
	var updateIndices = shadowUpdateIndices()
	Plotly.restyle('well_log_plot' ,{y: newys}, updateIndices)
	sendAppInfoFieldUpdate("dscache_dip", dip)
}

var mouseWheelZoomOn = function(onoff){
	Plotly.plot('well_log_plot',[],{},{scrollZoom: onoff})	
}

