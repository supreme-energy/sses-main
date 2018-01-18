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
		} else {
		   if(!isShadowOn){
		   	dipupdown(modifier, true )
		   } else{
		   	shadowDipUpDown(modifier)
		   }
		}
		return false;
	}
});

