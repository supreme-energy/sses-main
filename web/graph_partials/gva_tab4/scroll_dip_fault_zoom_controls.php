<?php ?>
var lastscrollEvent = null
var scrollMode = 'zoom'
graphDiv.on('plotly_hover', function(data){
	window.onwheel = function(e){
		if(scrollMode=='zoom'){
			return false;
		}
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
		   if(!isShadowOn){
		   	faultupdown(modifier,true)
		   } else {
			shadowFaultUpDown(modifier)
		   }
		} else if(scrollMode == 'dip') {
		   if(!isShadowOn){
		   	dipupdown(modifier, true )
		   } else{
		   	shadowDipUpDown(modifier)
		   }
		} else if(scrollMode == 'cbias'){
			modifier*=10
			if(!isShadowOn){
		   		biasupdown(modifier)
		   	} else{
		   		shadowBiasUpDown(modifier)
		   	}
		} else if(scrollMode == 'cscale'){
			modifier*=10
			if(!isShadowOn){
		   		factorupdown(modifier)
		   	} else{
		   		shadowScaleUpDown(modifier)
		   	}
		
		} else if(scrollMode == 'pbias'){
			modifier*=10
			allBiasUpDown(modifier)
		}
		return false;
	}
});

