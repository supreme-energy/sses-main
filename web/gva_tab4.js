/*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/
// gva_tab4.js
var ray={
ajax:function(st) { this.show('load'); },
show:function(el) { this.getID(el).style.display=''; },
getID:function(el) { return document.getElementById(el); }
}
function Init(viewrotds,stop,sleft,freeeze) {
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	if(viewrotds>0) document.getElementById('div1').scrollLeft=sleft;
	else document.getElementById('div1').scrollTop=stop;
	console.log(freeze)
	if(freeze<=0) {
		var scrolltop=document.getElementById("div1").scrollTop;
		var scrollleft=document.getElementById("div1").scrollLeft;
		var plotstart=document.pointform.plotstart.value;
		var plotend=document.pointform.plotend.value;
		var pheight=document.getElementById("clickimage").height;
		var dist=0.0;
		if(viewrotds>0) {
			dist=(plotend-plotstart)/pheight*(scrollleft-885);
			document.getElementById("dbgscrolltop").value=scrollleft.toFixed(0);
		} else {
			dist=(plotend-plotstart)/pheight*(scrolltop-885);
			document.getElementById("dbgscrolltop").value=scrolltop.toFixed(0);
		}
		document.getElementById("dbgscrolldist").value=-dist.toFixed(2);
		
	}
	showProposedFault();
}

function scrollAction(div){

	var viewrotds=parseInt(document.pointform.viewrotds.value);
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	scrolltop  = div.scrollTop;
	scrollleft = div.scrollLeft;
	var plotstart=document.pointform.plotstart.value;
	var plotend=document.pointform.plotend.value;
	var pheight=document.getElementById("clickimage").height;
	var scrolltopin = document.getElementById('scrolltop_input');
	var scrollleftin = document.getElementById('scrollleft_input');
	scrollleftin = scrollleft;
	scrolltopin.value = scrolltop;
	if(freeze<=0){
		var dist=0.0;
		if(viewrotds>0) {
			dist=(plotend-plotstart)/pheight*(scrollleft-885);
			document.getElementById("dbgscrolltop").value=scrollleft.toFixed(0);
		} else {
			dist=(plotend-plotstart)/pheight*(scrolltop-885);
			document.getElementById("dbgscrolltop").value=scrolltop.toFixed(0);
		}
		document.getElementById("dbgscrolldist").value=-dist.toFixed(2);		
	}
	showProposedFault();
}

function SetScroll(rowform)
{
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
}
function OnLasImport(rowform)
{
	SetScroll(rowform);
	t = 'welllogfilesel.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function OnRotateDS(rowform) {
	if(rowform.viewrotds.value==0) {
		rowform.scrollleft.value=document.getElementById("div1").scrollTop;
		rowform.scrolltop.value=document.getElementById("div1").scrollLeft;
		rowform.viewrotds.value="1";
	}
	else {
		rowform.scrolltop.value=document.getElementById("div1").scrollLeft;
		rowform.scrollleft.value=document.getElementById("div1").scrollTop;
		rowform.viewrotds.value="0";
	}
	t = 'setview.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function OnViewDS(rowform) {
	SetScroll(rowform);
	t = 'setview.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function OnDeleteDS()
{
	var r=confirm("Delete this data set?");
	if (r==true)
 	{
		t = 'welllogdel.php';
		t = encodeURI (t);
		document.deleteds.action = t;
		document.deleteds.submit();
		return ray.ajax();
 	}
}
function plotbiasupdown (rowform, val) {
	rowform.plotbias.value=parseFloat(rowform.plotbias.value)+parseFloat(val);
	SetScroll(rowform);
	OnSetPlotCfg(rowform);
}
function OnSetPlotCfg(rowform)
{
	SetScroll(rowform);
	t = 'setplotcfg.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function OnLogScale(rowform)
{
	SetScroll(rowform);
	var ischecked = document.getElementById("lscb").checked;
	if(ischecked==true) rowform.uselogscale.value='1';
	else rowform.uselogscale.value='0';
	t = 'setplotcfg.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function setzoomto (rowform, val) {
	SetScroll(rowform);
	rowform.zoom.value=val;
	t = 'setzoom.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function setzoom (rowform) {
	SetScroll(rowform);
	rowform.zoom.value=rowform.zoomtext.value;
	t = 'setzoom.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}

function showvalue(event,el){
  var top = 0, left = 0; 
  if (!event) { event = window.event; } 
  var myTarget = event.currentTarget; 
  if (!myTarget) { 
   myTarget = event.srcElement; 
  } 
  else if (myTarget == "undefined") { 
   myTarget = event.srcElement; 
  } 
  while(myTarget!= document.body) { 
     top += myTarget.offsetTop; 
     left += myTarget.offsetLeft; 
     myTarget = myTarget.offsetParent; 
  } 

	pos_x = (event.offsetX?(event.offsetX):event.pageX);
	pos_y = (event.offsetY?(event.offsetY):event.pageY);
	pos_x -= left;
	pos_y -= top;

	var plotstart=document.pointform.plotstart.value;
	var plotend=document.pointform.plotend.value;
	var scaleright=document.pointform.scaleright.value;
	var pheight=document.getElementById("clickimage").height;
	var pwidth=document.getElementById("clickimage").width;

	pos_y+=document.getElementById("div1").scrollTop;
	pos_x+=document.getElementById("div1").scrollLeft;

	var rot=document.pointform.viewrotds.value;
	var depth, value;
	if(rot>=1) {
		// take off gnuplot margin
		pos_y-=16; pheight-=32;
		depth=(pos_x*(plotend-plotstart)/pwidth)+parseFloat(plotstart);
		value=scaleright-(pos_y*(scaleright/pheight));
	}
	else {
		// take off gnuplot margin
		pos_x-=16; pwidth-=32;
		depth=(pos_y*(plotend-plotstart)/pheight)+parseFloat(plotstart);
		value=pos_x*(scaleright/pwidth);
	}
	// scrolltop=document.getElementById("div1").scrollTop;
	document.getElementById("dbgdepth").value=depth.toFixed(2);
}

function getpoint(event,el){
  var top = 0, left = 0; 
  if (!event) { event = window.event; } 
  var myTarget = event.currentTarget; 
  if (!myTarget) { 
   myTarget = event.srcElement; 
  } 
  else if (myTarget == "undefined") { 
   myTarget = event.srcElement; 
  } 
  while(myTarget!= document.body) { 
     top += myTarget.offsetTop; 
     left += myTarget.offsetLeft; 
     myTarget = myTarget.offsetParent; 
  } 

	pos_x = (event.offsetX?(event.offsetX):event.pageX);
	pos_y = (event.offsetY?(event.offsetY):event.pageY);
	pos_x -= left;
	pos_y -= top;

	var plotstart=document.pointform.plotstart.value;
	var plotend=document.pointform.plotend.value;
	var scaleright=document.pointform.scaleright.value;
	var pheight=document.getElementById("clickimage").height;
	var pwidth=document.getElementById("clickimage").width;

	pos_y+=document.getElementById("div1").scrollTop;
	pos_x+=document.getElementById("div1").scrollLeft;
	// document.getElementById("dbgdepth").value=pos_y;

	var rot=document.pointform.viewrotds.value;
	var depth, value;
	if(rot>=1) {
		depth=(pos_x*(plotend-plotstart)/pwidth)+parseFloat(plotstart);
		value=pos_y*(scaleright/pheight);
	}
	else {
		depth=(pos_y*(plotend-plotstart)/pheight)+parseFloat(plotstart);
		value=pos_x*(scaleright/pwidth);
	}

	document.pointform.scrolltop.value=document.getElementById("div1").scrollTop;
	document.pointform.scrollleft.value=document.getElementById("div1").scrollLeft;
	document.pointform.depth.value=depth;
	document.pointform.val.value=value;
	document.pointform.setflag.value=1;

	// alert("depth: " + depth);
	document.getElementById("dbgdepth").value=depth.toFixed(2);
	// document.getElementById("dbgdepth").value=pos_x;

	if( document.pointform.editmode.value=="align" ) {
		document.pointform.val.value="";
		t = 'gva_tab4.php';
		t = encodeURI (t);
		document.pointform.action = t;
		document.pointform.submit();
		return ray.ajax();
	}
	else if( document.pointform.editmode.value=="trim" ) {
		document.pointform.val.value="";
		t = 'gva_tab4.php';
		t = encodeURI (t);
		document.pointform.action = t;
		document.pointform.submit();
		return ray.ajax();
	}
	else {
		var topsel=document.pointform.dsseltop.value;
		var botsel=document.pointform.dsselbot.value;
		if(topsel>botsel) {
			var t=topsel;
			topsel=botsel;
			botsel=t;
		}
		if( depth>=topsel && depth<=botsel ) {
			t = 'gva_tab4.php';
			t = encodeURI (t);
			document.pointform.action = t;
			document.pointform.submit();
			return true;
		}
		else {
			document.pointform.editmode.value="search";
			document.pointform.scrolltop.value="";
			document.pointform.scrollleft.value="";
			t = 'gva_tab4.php';
			t = encodeURI (t);
			document.pointform.action = t;
			document.pointform.submit();
			return true;
		}
	}
}
function directInput(rowform) {
	var topsel=document.directinput.dsseltop.value;
	var botsel=document.directinput.dsselbot.value;
	var depth=rowform.depth.value;
	if(topsel>botsel) {
		var t=topsel;
		topsel=botsel;
		botsel=t;
	}
	if( depth>=topsel && depth<=botsel ) {
		t = 'gva_tab4.php';
		t = encodeURI (t);
		document.directinput.action = t;
		document.directinput.submit();
		return true;
	}
	else {
		document.directinput.editmode.value="search";
		document.directinput.scrolltop.value="";
		document.directinput.scrollleft.value="";
		t = 'gva_tab4.php';
		t = encodeURI (t);
		document.directinput.action = t;
		document.directinput.submit();
		return true;
	}
}
function setTrimMode() {
	if(document.pointform.editmode.value!="trim") {
		document.pointform.editmode.value="trim";
		document.getElementById("btntrim").value="Click To Turn Trim Off";
		alert("Trim mode ON\nClick the depth to trim the data section to...");
	}
	else {
		document.pointform.editmode.value="";
		document.getElementById("btntrim").value="Trim Section";
	}
}
function setAlignMode() {
	if(document.pointform.editmode.value!="align") {
		document.pointform.editmode.value="align";
		document.getElementById("btnalign").value="Click To Turn Alignment Off";
		alert("Align mode is ON\nClick on a depth to align the selected point to...\n");
	}
	else {
		document.pointform.editmode.value="";
		document.getElementById("btnalign").value="Align Welllog";
	}
}
function showProposedFault() {
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	var dbfault=document.pointform.dbfault.value;
	var dist=0.0;
	if(freeze==0) {
		var viewrotds=parseInt(document.pointform.viewrotds.value);
		var plotstart=document.pointform.plotstart.value;
		var plotend=document.pointform.plotend.value;
		var pheight=document.getElementById("clickimage").height;
		var pwidth=document.getElementById("clickimage").width;
		var scrolltop=document.getElementById("div1").scrollTop;
		var scrollleft=document.getElementById("div1").scrollLeft;
		if(viewrotds>0) dist=(plotend-plotstart)/pwidth*(scrollleft-885)*-1;
		else dist=(plotend-plotstart)/pheight*(scrolltop-885)*-1;
	} else {
		dist=document.pointform.dscache_fault.value;
	}
	var fault=parseFloat(dbfault)+parseFloat(dist);
	document.getElementById("dbgscrollfault").value=fault.toFixed(2);
	return dist;
}
function showdistance(event,el){
	var viewrotds=parseInt(document.pointform.viewrotds.value);
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	var plotstart=document.pointform.plotstart.value;
	var plotend=document.pointform.plotend.value;
	var pheight=document.getElementById("clickimage").height;
	var scrolltop=document.getElementById("div1").scrollTop;
	var scrollleft=document.getElementById("div1").scrollLeft;
	if(freeze==0) {
		if(viewrotds>0) var dist=(plotend-plotstart)/pheight*(scrollleft-885);
		else var dist=(plotend-plotstart)/pheight*(scrolltop-885);
		document.getElementById("dbgscrolldist").value=-dist.toFixed(2);
	}
	showProposedFault();
	if(viewrotds>0) document.getElementById("dbgscrolltop").value=scrollleft.toFixed(0);
	else document.getElementById("dbgscrolltop").value=scrolltop.toFixed(0);
}
function setdscache (rowform, param, val) {
	if(param=="dip") {
		var t = parseFloat(rowform.dscache_dip.value);
		t+=val;
		if(t<-89.9) t=-89.9;
		if(t>89.9)	t=89.9;
		rowform.dscache_dip.value=t;
	}
	if(param=="bias") {
		var t = parseFloat(rowform.dscache_bias.value);
		t+=val;
		rowform.dscache_bias.value=t;
	}
	if(param=="scale") {
		//var t = parseFloat(rowform.dscache_scale.value);
		//t+=val;
		//rowform.dscache_scale.value=t;
	}
	if(param=="freeze") {
		if(rowform.freeze.checked==1) {
			rowform.dscache_md.value=rowform.endmd.value;
			rowform.dscache_freeze.value="1";
			var dist=showProposedFault();
			rowform.dscache_fault.value=dist.toFixed(2);
		} else {
			document.pointform.dscache_freeze.value="0";
			rowform.dscache_freeze.value="0";
			scrollAction(document.getElementById('div1'));
		}
	}
	SetScroll(rowform);
	t = 'setdscache.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function savedscache(bDoFault) {
	rowform = document.getElementById('savedscache');
	SetScroll(rowform);
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	var dbfault=document.pointform.dbfault.value;
	var dist=document.pointform.dscache_fault.value;
	var fault=parseFloat(dbfault)+parseFloat(dist);
	rowform.dscache_fault.value=fault.toFixed(3);
	var str="Save shadow dip value of "+rowform.dscache_dip.value+"\n";
	if(bDoFault==1) {
		str+="and fault to "+rowform.dscache_fault.value+"\n";
	}
	else rowform.dscache_fault.value="";
	str+="to "+rowform.viewdspcnt.value+" data sets\n";
	str+="starting at depth "+rowform.dscache_md.value+"\n\n";
	str+="Are you sure you want to do this?";
	var r=confirm(str);
	if(r!=true) return;
	SetScroll(rowform);
	t = 'savedscache.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}

function dipupdown (rowform, val) {
	SetScroll(rowform);
	var t = parseFloat(rowform.sectdip.value);
	t+=val;
	if(t<-89.9) t=-89.9;
	if(t>89.9)	t=89.9;
	rowform.sectdip.value=t;
	setdscfg(rowform);
}
function faultupdown (rowform, val) {
	SetScroll(rowform);
	var t = parseFloat(rowform.sectfault.value);
	t+=val;
	rowform.sectfault.value=t;
	setdscfg(rowform);
}
function biasupdown (rowform, val) {
	SetScroll(rowform);
	rowform.bias.value=parseFloat(rowform.bias.value)+parseFloat(val);
	setdscfg(rowform);
}
function scaleupdown (rowform, val) {
	SetScroll(rowform);
	var t = parseFloat(rowform.factor.value);
	t+=val;
	if(t<0) t=0;
	if(t>100)	t=100;
	rowform.factor.value=t;
	setdscfg(rowform);
}
function setdscfg (rowform) {
	SetScroll(rowform);
	t = 'setdscfg.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
