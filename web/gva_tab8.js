var scrllcnt=0;
var gdist=0;

function Init(viewrotds,stop,sleft) {
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	if(viewrotds>0) document.getElementById('div1').scrollLeft=sleft;
	else document.getElementById('div1').scrollTop=stop;

	document.getElementById('div1').scrollTop=stop;

	//alert('viewrotds=' + viewrotds + ' stop=' + stop + ' sleft=' + sleft + ' freeze=' + freeze +
	//	' new scroll top=' + document.getElementById('div1').scrollTop);
	
	if(freeze<=0) {
		var scrolltop=document.getElementById("div1").scrollTop;
		var scrollleft=document.getElementById("div1").scrollLeft;
		var plotstart=document.pointform.plotstart.value;
		var plotend=document.pointform.plotend.value;
		var pheight=document.getElementById("clickimage").height;
		var dist=0.0;
		if(viewrotds>0) {
			dist=(plotend-plotstart)/pheight*(scrollleft-885);
			dist=dist+parseFloat(document.pointform.dspoffset.value);
			if(document.getElementById("dbgscrolltop")!=undefined)
				document.getElementById("dbgscrolltop").value=scrollleft.toFixed(0);
		} else {
			dist=(plotend-plotstart)/pheight*(scrolltop-885);
			dist=dist+parseFloat(document.pointform.dspoffset.value);
			if(document.getElementById("dbgscrolltop")!=undefined)
				document.getElementById("dbgscrolltop").value=scrolltop.toFixed(0);
		}
		gdist=dist	
		if(document.getElementById("dbgscrolldist")!=undefined)
			document.getElementById("dbgscrolldist").value=-dist.toFixed(2);
		showProposedFault();	
	}
}

function scrollAction(div){
	if(scrllcnt>0){
		console.log('scrolling');
		var viewrotds=parseInt(document.pointform.viewrotds.value);
		var freeze=parseInt(document.pointform.dscache_freeze.value);
		scrolltop  = div.scrollTop;
		scrollleft = div.scrollLeft;
		var plotstart=document.pointform.plotstart.value;
		var plotend=document.pointform.plotend.value;
		var pheight=document.getElementById("clickimage").height;
		var scrolltopin = document.getElementById('scrolltop_input');
		var scrollleftin = document.getElementById('scrollleft_input');
		//scrollleftin = scrollleft;
		//scrolltopin.value = scrolltop;
		if(freeze<=0){
			var dist=0.0;
			if(viewrotds>0) {
				dist=(plotend-plotstart)/pheight*(scrollleft-885);
				document.getElementById("dbgscrolltop").value=scrollleft.toFixed(0);
			} else {
				dist=(plotend-plotstart)/pheight*(scrolltop-885);
				document.getElementById("dbgscrolltop").value=scrolltop.toFixed(0);
			}		
			showProposedFault();
		} 
	}
	scrllcnt++;
}

function SetScroll(rowform)
{
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
}

function ClearScroll(rowform){
	rowform.scrolltop.value='';
	rowform.scrollleft.value='';
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
		t = 'gva_tab8.php';
		t = encodeURI (t);
		document.pointform.action = t;
		document.pointform.submit();
		return ray.ajax();
	}
	else if( document.pointform.editmode.value=="trim" ) {
		document.pointform.val.value="";
		t = 'gva_tab8.php';
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
			t = 'gva_tab8.php';
			t = encodeURI (t);
			document.pointform.action = t;
			document.pointform.submit();
			return true;
		}
		else {
			document.pointform.editmode.value="search";
			document.pointform.scrolltop.value="";
			document.pointform.scrollleft.value="";
			t = 'gva_tab8.php';
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
		t = 'gva_tab8.php';
		t = encodeURI (t);
		document.directinput.action = t;
		document.directinput.submit();
		return true;
	}
	else {
		document.directinput.editmode.value="search";
		document.directinput.scrolltop.value="";
		document.directinput.scrollleft.value="";
		t = 'gva_tab8.php';
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

// MARK: Here is where we calculate how far the data has been scrolled
// MARK: giving us a "fault" value to use
function showProposedFault() {
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	
	var lastdist = gdist*-1;
	var dist=0.0;
	if(freeze==0) {
		var viewrotds=parseInt(document.pointform.viewrotds.value);
		var plotstart=document.pointform.plotstart.value;
		var plotend=document.pointform.plotend.value;
		var pheight=document.getElementById("clickimage").height;
		var pwidth=document.getElementById("clickimage").width;
		var scrolltop=document.getElementById("div1").scrollTop;
		var scrollleft=document.getElementById("div1").scrollLeft;
		var scrollwidth=document.getElementById("div1").scrollWidth;
		if(viewrotds>0) dist=(plotend-plotstart)/pwidth*(scrollleft-885)*-1;
		else dist=(plotend-plotstart)/pheight*(scrolltop-885)*-1;
		dist=dist-parseFloat(document.pointform.dspoffset.value);
	} else {
		dist=document.pointform.dscache_fault.value;
	}
	// console.log('last dist:'+lastdist);
	// console.log('new dist:'+dist);
	gdist = dist*-1;
	
	if(document.getElementById("dbgscrollfault")!=undefined){
		var dbfault=document.getElementById("dbgscrollfault").value;
		var fault=parseFloat(dbfault)+(dist-lastdist);
		document.getElementById("dbgscrollfault").value=fault.toFixed(2);
	}
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
	var scrollwidth=document.getElementById("div1").scrollWidth;

	//alert('freeze=' + freeze + ' plotstart=' + plotstart + ' plotend=' + plotend + ' pheight=' + pheight +
	//	' scrolltop=' + scrolltop + ' scrollwidth=' + scrollwidth);

	if(freeze==0) {
		if(viewrotds>0) var dist=(plotend-plotstart)/pheight*(scrollleft-885);
		else var dist=(plotend-plotstart)/pheight*(scrolltop-885);
		if(viewrotds<=0)
			dist=dist+parseFloat(document.pointform.dspoffset.value);
		if(document.getElementById("dbgscrolldist")!=undefined)
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
		var t = parseFloat(rowform.dscache_scale.value);
		t+=val;
		rowform.dscache_scale.value=t;
	}
	if(param=="freeze") {
		if(rowform.freeze.checked==1) {
			faultmod = my_dbfault;
			//alert('faultmod=' + faultmod);
			scrlldiv= document.getElementById('div1')
			SetScroll(rowform);
			rowform.dscache_md.value=rowform.endmd.value;
			rowform.dscache_freeze.value="1";
			var dist=showProposedFault();
			rowform.dscache_fault.value=(document.getElementById("dbgscrollfault").value-faultmod);
			rowform.dsholdfault.value='0';
		} else {
			rowform.dscache_freeze.value="0";
			if(rowform.dsholdfault.value<=0){
				rowform.dscache_fault.value ="0";
				SetScroll(rowform);
			} 
		}
	}
	if(param=='reset'){
		rowform.dscache_freeze.value="0";
		rowform.dscache_fault.value="0";
		ClearScroll(rowform);
	}
	if(param=='holdfault'){
		if(rowform.holdfault.checked==1){
			rowform.dsholdfault.value=document.getElementById("div1").scrollTop;
		} else {
			rowform.dsholdfault.value='0';
		}
	}
	t = 'setdscache.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}

function savedscache(bDoFault) {
	var rowform=document.getElementById("savedscache");
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
	if(rowform) {
		SetScroll(rowform);
		t = 'setdscfg.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
		return ray.ajax();
	} else {
		rowform = document.getElementById('dipform');
		t = 'setdscfg.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
		return ray.ajax();
	}
}

function OnSubmit(rowform) {
    t = 'splotconfigd.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return true;
}

function OnImport(rowform)
{
    t = 'surveysfilesel.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return true;
//	return ray.ajax();
}

function OnSurvey(rowform)
{
	t = 'surveychange.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}

function OnSurveyPDF(rowform)
{
	var phpcall="surveypdf.php?seldbname=" + document.getElementById("seldbn").value;
	// newwindow=window.open('surveypdf.php');
	newwindow=window.open(phpcall);
	if (window.focus) {newwindow.focus()}
	return false;
}

function OnSurveyCSV(rowform)
{
	var phpcall="surveycsv.php?seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall);
	if (window.focus) {newwindow.focus()}
	return false;
}

function OnSurveyPlotVS()
{
	var phpcall="surveyplotvs.php?seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall,'VerticalSection',
		'height=950,width=1250,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}

function OnSurveyPrint()
{
	var phpcall="surveyprint.php?seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall,'SurveyPrintout',
		'height=650,width=950,left=100,top=0,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}

function OnSurveyPlotLateral(rowform)
{
	var phpcall="surveyplotlat.php?seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall,'PolarPlot',
		'height=950,width=1250,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}

function OnSurveyPlotPolar()
{
	var phpcall="surveyplotpolar.php?seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall,'PolarPlot',
		'height=850,width=950,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
	// window.open ('surveyplotpolar.php');
	// return true;
}

function OnSurveyPlot3D() {
	var phpcall="surveyplot3d.php?xaxis=70&zaxis=10&seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall,'3DPlot',
		'height=950,width=1050,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}

function noshowline() {
	var numprojs=document.getElementById("numprojs").value;
	var i;
	for(i=0;i<numprojs;i++)  {
		var emd=document.getElementById("gridmd"+i);
		var einc=document.getElementById("gridinc"+i);
		var eazm=document.getElementById("gridazm"+i);
		var etvd=document.getElementById("gridtvd"+i);
		var evs=document.getElementById("gridvs"+i);
		var ens=document.getElementById("gridns"+i);
		var eew=document.getElementById("gridew"+i);
		var ecd=document.getElementById("gridcd"+i);
		var eca=document.getElementById("gridca"+i);
		var edl=document.getElementById("griddl"+i);
		var ecl=document.getElementById("gridcl"+i);
		var etpos=document.getElementById("gridtpos"+i);
		var etot=document.getElementById("gridtot"+i);
		var ebpos=document.getElementById("gridbpos"+i);
		var ebot=document.getElementById("gridbot"+i);
		var edip=document.getElementById("griddip"+i);
		var efault=document.getElementById("gridfault"+i);
		emd.setAttribute('class', 'gridproj gridmdcl');
		einc.setAttribute('class', 'gridproj gridmdcl');
		eazm.setAttribute('class', 'gridproj gridmdcl');
		etvd.setAttribute('class', 'gridproj gridmdcl');
		evs.setAttribute('class', 'gridproj gridmdcl');
		ens.setAttribute('class', 'gridproj gridmdcl');
		eew.setAttribute('class', 'gridproj gridmdcl');
		ecd.setAttribute('class', 'gridproj gridmdcl');
		eca.setAttribute('class', 'gridproj gridmdcl');
		edl.setAttribute('class', 'gridproj gridmdcl');
		ecl.setAttribute('class', 'gridproj gridmdcl');
		etpos.setAttribute('class', 'gridproj gridtclbot');
		etot.setAttribute('class', 'gridproj gridtclbot');
		try{
			ebpos.setAttribute('class', 'gridproj gridtclbot');
		}catch(e){}
		ebot.setAttribute('class', 'gridproj gridtclbot');
		edip.setAttribute('class', 'gridproj gridtclbot');
		efault.setAttribute('class', 'gridproj gridtclbot');
		// clrVisible('layer1');
	}
}

function showline(i) {

	noshowline();
	
	var emd=document.getElementById("gridmd"+i);
	var einc=document.getElementById("gridinc"+i);
	var eazm=document.getElementById("gridazm"+i);
	var etvd=document.getElementById("gridtvd"+i);
	var evs=document.getElementById("gridvs"+i);
	var ens=document.getElementById("gridns"+i);
	var eew=document.getElementById("gridew"+i);
	var ecd=document.getElementById("gridcd"+i);
	var eca=document.getElementById("gridca"+i);
	var edl=document.getElementById("griddl"+i);
	var ecl=document.getElementById("gridcl"+i);
	var etpos=document.getElementById("gridtpos"+i);
	var etot=document.getElementById("gridtot"+i);
	var ebpos=document.getElementById("gridbpos"+i);
	var ebot=document.getElementById("gridbot"+i);
	var edip=document.getElementById("griddip"+i);
	var efault=document.getElementById("gridfault"+i);
	emd.setAttribute('class', 'gridro');
	einc.setAttribute('class', 'gridro');
	eazm.setAttribute('class', 'gridro');
	etvd.setAttribute('class', 'gridro');
	evs.setAttribute('class', 'gridro');
	ens.setAttribute('class', 'gridro');
	eew.setAttribute('class', 'gridro');
	ecd.setAttribute('class', 'gridro');
	eca.setAttribute('class', 'gridro');
	edl.setAttribute('class', 'gridro');
	ecl.setAttribute('class', 'gridro');
	etpos.setAttribute('class', 'gridro');
	etot.setAttribute('class', 'gridro');
	try{
		ebpos.setAttribute('class', 'gridro');
	}catch(e){}
	ebot.setAttribute('class', 'gridro');
	edip.setAttribute('class', 'gridro');
	efault.setAttribute('class', 'gridro');
}

function OnAvgDip() {
	var phpcall="projavgdip.php?seldbname=" + document.getElementById("seldbn").value;
	var newwindow=window.open(phpcall, 'Average Dip Calulator',
		'height=320,width=400,left=500,top=60,scrollbars=no,location=no,resizable=no');
	if (window.focus) {newwindow.focus()}
	return false;
}

function projws(myform) {
    var l=0;
	var t=window.screenTop;
	if (! window.focus) return true;
	window.open('', 'Projection Worksheet',
		'height=260,width=1020,left=100,top='+t +', scrollbars=yes, location=no, resizable=no');
	myform.target='Projection Worksheet';
	return false;
}

function oujiaws(myform) {
    var l=0;
	var t=window.screenTop;
	if (! window.focus) return true;
	window.open('','Oujia Worksheet',
		'height=600,width=1020,left=100,top='+t +', scrollbars=yes, location=yes, resizable=yes');
		myform.target='Oujia Worksheet';
	return false;
}

function fixedlanding(myform)
{
    var l=0;
	var t=window.screenTop;
	if (! window.focus)return true;
	window.open('', 'Fixed Landing',
		'height=350,width=585,left=100,top='+ t +', scrollbars=yes, location=yes, resizable=yes');
	myform.target='Fixed Landing';
	return false;
}

function OnClearChecks()
{
    var numsvys=parseFloat(document.getElementById("numsvys").value);
	var numprojs=parseFloat(document.getElementById("numprojs").value);
	numsvys+=numprojs;
	for(i=0; i<numsvys; i=i+1) {
		fid="f"+i;
		form=document.getElementById(fid);
		form.del.checked=0;
	}
}

function OnAvgDip()
{
    var phpcall="projavgdip.php?seldbname=" + document.getElementById("seldbn").value;
	var newwindow=window.open(phpcall,'Average Dip Calulator',
		'height=320,width=400,left=500,top=60,scrollbars=no,location=no,resizable=no');
	if (window.focus)
	{
		newwindow.focus()
	}
	return false;
}


function OnSetChecks()
{
	var numsvys=parseFloat(document.getElementById("numsvys").value);
	var numprojs=parseFloat(document.getElementById("numprojs").value);
	numsvys+=numprojs;
	for(i=0; i<numsvys; i=i+1) {
		fid="f"+i;
		form=document.getElementById(fid);
		form.del.checked=1;
	}
}

function OnDelSurveys()
{
	var numsvys=parseFloat(document.getElementById("numsvys").value);
	var numprojs=parseFloat(document.getElementById("numprojs").value);
	var high_num = parseFloat(document.getElementById("high_num").value)
	var high_cl  = parseFloat(document.getElementById("high_cl").value)
	numsvys+=numprojs;
	var do_cldel = false;
	var sids="";
	num = -1;
	for(i=0; i<numsvys; i=i+1) {
		fid="f"+i;
		form=document.getElementById(fid);
		dodel=form.del.checked;
		id=form.id.value;
		try{
			num = parseFloat(form.num.value);
		} catch(e){}
		if(dodel>0) {
			if(sids=="") sids=id;
			else sids=sids+","+id;
			if(high_num==num){
				do_cldel=true;
			}
		}
	}
	if(sids=="")	return;
	rowform=document.getElementById("delsvys");
	rowform.sids.value=sids;
	if(do_cldel){
		rowform.deccl.value='t';
		rowform.deccl_m.value=high_cl;
	}else {
		rowform.deccl.value='f';
		rowform.deccl_m.value=0;
	}
	result=confirm("Delete these surveys, Are you sure?");
	if(result)
	{
		t = 'surveydel.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return ray.ajax();
	}
}
