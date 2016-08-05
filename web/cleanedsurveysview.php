<?php
 require_once("dbio.class.php");
$select_box_s=''; 
$select_box_e='';
$survey_html='';
 function PrintSurveys() {
	global $db,$group_id,$select_box_s,$select_box_e,$survey_html;
	if($group_id){
	$query = "select * from deleted_survey_data where group_id = $group_id;";
	$db->DoQuery($query);
	$cnt = $db->FetchNumRows();
	$i=0;

	while($row = $db->FetchRow()){
		$md=sprintf("%.2f", $row['md']);
		if($i==0){
			$md0=$md;
		}
		$select_box_s.="<option value='$md' >$md</option>";
		if(($i+1)>=$cnt){
			$select_box_e.="<option value='$md' selected>$md</option>";
		} else {
			$select_box_e.="<option value='$md'>$md</option>";
		}
		$inc=sprintf("%.2f", $row['inc']);
		$azm=sprintf("%.2f", $row['azm']);
		$tvd=$row['tvd'];
		$ns=sprintf("%.2f", $row['ns']);
		$ew=sprintf("%.2f", $row['ew']);
		$vs=sprintf("%.2f", $row['vs']);
		$dl=sprintf("%.2f", $row['dl']);
		$cl=sprintf("%.2f", $row['cl']);
		$dip=sprintf("%.2f", $row['dip']);
		$fault=sprintf("%.2f", $row['fault']);
		$survey_html.= "<TR>";
		$survey_html.= "<TD class='grid gridmdcl'>";
		$survey_html.= "$md";
		$survey_html.= "</TD> <TD class='grid gridmdcl'>";
		$survey_html.="$inc";
		$survey_html.="</TD> <TD class='grid gridmdcl'>";
		$survey_html.="$azm";
		$survey_html.= "</TD>";
		if($i%4<=1) $tdcls="<TD class='gridro2 gridmdcl'>";
		else $tdcls="<TD class='gridro gridmdcl'>";
		$survey_html.="$tdcls";
		$survye_html.=sprintf("%.2f</TD>", $tvd);
		$survey_html.="$tdcls $vs</TD>";
		$survey_html.="$tdcls $ns</TD>";
		$survey_html.="$tdcls $ew</TD>";
		$survey_html.="$tdcls $dl</TD>";
		$survey_html.="$tdcls $cl</TD>";
		$survey_html.="$tdcls $dip</TD>";
		$survey_html.="$tdcls $fault</TD>";
		$survey_html.= "</TR>";
		$i++;
	}
		$query= "select * from surveys where md < $md0 order by md limit 1;";
		$db->DoQuery($query);
		$row = $db->FetchRow();
		$md = $row['md'];
		$select_box_s="<option value='$md' selected>$md</option>".$select_box_s;
	} else{
		$survey_html="<td colspan='7'>No data to display</td>";
	}
	
}
if("$seldbname"=="")	$seldbname=$_GET['seldbname'];
$group_id = $_REQUEST['grpid'];
$db=new dbio($seldbname);
$db->OpenDb();
$query="select * from deleted_survey_group order by id desc";
$select_box="Select Deleted Data by Timestamp:<select id='grp_select' onchange='load_del_group()'>";
$db->DoQuery($query);

while($row = $db->FetchRow()){
	$selected = '';
	if(!$group_id){
		$group_id=$row['id'];
	}
	if($row['id']==$group_id){
		$selected='selected';
	} else {
		$selected='';
	}
	$select_box.="<option $selected value=".$row['id'].">".$row['created']."</option>";
}
$select_box.="</select>";
$disabledbuttons = $group_id?'':'disabled';
PrintSurveys();
?>
<HTML>
<HEAD>
<TITLE>Cleaned Surveys</TITLE>
<LINK rel='stylesheet' type='text/css' href='gva_tab3.css'/>
</HEAD>
<style type='text/css'>
.x-mask {
	z-index: 100;
	position: absolute;
	top: 0;
	left: 0;
	filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=50 );
	opacity: 0.5;
	width: 100%;
	height: 100%;
	zoom: 1;
	background: #cccccc
}

.x-mask-msg {
	z-index: 20001;
	position: absolute;
	top: 0;
	left: 0;
	padding: 2px;
	border: 1px solid;
	border-color: #d0d0d0;
	background-image: none;
	background-color: #e0e0e0
}

.x-mask-msg div {
	padding: 5px 10px 5px 25px;
	background-image:
		url('../../resources/themes/images/gray/grid/loading.gif');
	background-repeat: no-repeat;
	background-position: 5px center;
	cursor: wait;
	border: 1px solid #b3b3b3;
	background-color: #eeeeee;
	color: #222222;
	font: normal 11px tahoma, arial, verdana, sans-serif
}
</style>
<script type="text/javascript" src='ext-all.js'></script>
<body>
<script>
	var myMask = new Ext.LoadMask(Ext.getBody(), {msg:"Loading. Please wait..."});
	load_del_group=function(){
		myMask.show()
		grpid=document.getElementById('grp_select').value
		window.location='cleanedsurveysview.php?seldbname=<?echo $seldbname?>&grpid='+grpid
	}
	reimportoverrange=function(without){
		without = without?without:false;
		grpid=document.getElementById('grp_select').value
		sdepth=document.getElementById('sdepth').value
		edepth=document.getElementById('edepth').value
		newurl = '?seldbname=&groupid='+grpid+'&sdepth='+sdepth+'&edepth='+edepth
		myMask.show();
		if(window.opener && !window.opener.closed){
//alert('opener 7');
			window.opener.callMask(true);
		}
		else if(window.opener.opener && !window.opener.opener.closed){
//alert('opener 8');
			window.opener.opener.callMask(true);
		}
		if(without){
			grpid=false;
		}
		Ext.Ajax.request({
			url: 'json/survey_load_in_range.php',
			params:{seldbname:'<?echo $seldbname?>',groupid:grpid,sdepth:sdepth,edepth:edepth,wo:without},
			failure:function(){
				alert("an error occured the prevented the full completion of the import please verify data import results");
					if(window.opener && !window.opener.closed){
//alert('opener 1');
						window.opener.callMask(false);
					}
					else if(window.opener.opener && !window.opener.opener.closed){
//alert('opener 2');
						window.opener.opener.callMask(false);
					}
					myMask.hide()
			},
			success:function(resp){
				nocheck=false;
				json = Ext.decode(resp.responseText)
				if(json.surveys.length>0){
					message='The following surveys were loaded'+"\n";
					for(i in json.surveys){
						s=json.surveys[i]
						message+='survey md:'+s.md+' inc:'+s.inc+' azm:'+s.azm+"\n";
					
					}
					alert(message);
					if(window.opener && !window.opener.closed){
//alert('opener 3');
						window.opener.location.reload();
					}
					else if(window.opener.opener && !window.opener.opener.closed){
//alert('opener 4');
						window.opener.opener.location.reload();
					}
				} else {
					alert('No missing surveys in this depth range');
					if(window.opener && !window.opener.closed){
//alert('opener 5');
						window.opener.callMask(false);
					}
					else if(window.opener.opener && !window.opener.opener.closed){
//alert('opener 6');
						window.opener.opener.callMask(false);
					}
				}
				
				myMask.hide()
			}
		})
	}
</script>
<div><?echo $select_box ?></div>
<div><select id='sdepth' name='sdepth' style='display:none'><?echo $select_box_s?></select><select id='edepth' name='edepth' style='display:none'><?echo $select_box_e?></select> <button <?echo $disabledbuttons?> onclick="reimportoverrange(false)">reimport with dip/fault</button><button <?echo $disabledbuttons?> onclick="reimportoverrange(true)">reimport without dip/fault</button>
<TABLE class="surveys">
	<TR> 
	<TH class='surveys'>Depth</TH>
	<TH class='surveys'>Inc</TH>
	<TH class='surveys'>Azm</TH>
	<TH class='surveys'>TVD</TH>
	<TH class='surveys'>VS</TH>
	<TH class='surveys'>NS</TH>
	<TH class='surveys'>EW</TH>
	<TH class='surveys'>DL</TH>
	<TH class='surveys'>CL</TH>
	<TH class='rot'>Dip</TH>
	<TH class='rot'>Fault</TH>
	</TR>
	<?php	
	echo $survey_html;
	$db->CloseDb();
	?>
	<TR> 
	<TH class='surveys'>Depth</TH>
	<TH class='surveys'>Inc</TH>
	<TH class='surveys'>Azm</TH>
	<TH class='surveys'>TVD</TH>
	<TH class='surveys'>VS</TH>
	<TH class='surveys'>NS</TH>
	<TH class='surveys'>EW</TH>
	<TH class='surveys'>DL</TH>
	<TH class='surveys'>CL</TH>
	<TH class='rot'>Dip</TH>
	<TH class='rot'>Fault</TH>
	
	</TR>
	</TABLE>
</body>
</HTML>
