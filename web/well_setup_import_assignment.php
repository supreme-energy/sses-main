<?php 
include_once("sses_include.php");
require_once("dbio.class.php");
require_once("version.php");
$seldbname=$_REQUEST['seldbname'];
$fromtiein = filter_var($_REQUEST['fromtiein'], FILTER_VALIDATE_BOOLEAN);
$db=new dbio($seldbname);
$db->OpenDb();
?>
<script>
<?php include "graph_partials/shared/assignment_definition_js_vars.php"?>
var import_survy_cnt = 0;
</script>
<table id='main_content_table'>
    <tr>
        <td>SGTA Name</td><td><div id='var_name_selector'></div></td>
    </tr>
    <tr>
        <td>Import Value</td><td><input type='text' id='selected_var_value'></td>
    </tr>
    <tr>
        <td>Source File</td><td><input type='text' disabled id='selected_var_source_file'></td>
    </tr>
    <tr>
        <td>Source Column</td><td><input size=4 type='text' disabled id='selected_var_source_col'></td>
    </tr>
    <tr>
        <td>Source Row</td><td><input size=4 type='text' disabled id='selected_var_source_row'></td>
    </tr>
    <tr>
        <td><button onclick='clearVariable()'>clear selection</button></td>
        <td id='selection_lock' style='background-color:green;text-align:center;'>
        	<button id='lock_selection_lock' onclick='lockSelection()'>lock selection</button>
        	<button id='unlock_selection_lock' style='display:none' onclick='unlockSelection()'>unlock selection</button>
        </td>
    </tr>
    <tr>
        <?php if($fromtiein){?>
        <td><button onclick='wellLogPost()'>Import Selections</button></td>
        <?php } else {?>
        <td><button onclick='findNextUnAssigned()'>Next Unassigned</button></td>
        <?php }?>
    </tr>
</table>

<script>
var currentSelectedVariable = function(){
  return sharedVars['selectable_definitions'][bvnSelector.options[bvnSelector.selectedIndex].value]
}

var setVariableField = function(id, value){
  var field = document.getElementById(id)
  field.value = value
}
var setLock = function(lock){
  if(lock == 1){
    document.getElementById('selection_lock').style.backgroundColor='red'
    document.getElementById('lock_selection_lock').style.display='none'
    document.getElementById('unlock_selection_lock').style.display=''
  } else {
    document.getElementById('selection_lock').style.backgroundColor='green'
    document.getElementById('lock_selection_lock').style.display=''
    document.getElementById('unlock_selection_lock').style.display='none'
  }
}
var lockSelection = function(){
  var selected = currentSelectedVariable()
  selected.locked = 1
  localStorage.setItem(selected.display_name+"_lock", 1)
  setLock(1)
  selected.dbStore()
}

var unlockSelection = function(){
  var selected = currentSelectedVariable()
  selected.locked = 0
  localStorage.setItem(selected.display_name+"_lock", 0)
  setLock(0)
}

var clearVariable = function(){
  var selected = currentSelectedVariable()
  selected.column  = ''
  selected.filename= ''
  selected.row = ''
  selected.value = ''
    
  setVariableField('selected_var_value','')
  setVariableField('selected_var_source_file', '')
  setVariableField('selected_var_source_col', '')
  setVariableField('selected_var_source_row', '')
  localStorage.setItem(selected.display_name, JSON.stringify([])) 
}

var findNextUnAssignedLoop = function(startidx){
    var found = -1
    for(var i = startidx; i < bvnSelector.options.length; i ++){
        if(i == parseInt(bvnSelector.selectedIndex)){ continue;}
        var el = sharedVars['selectable_definitions'][bvnSelector.options[i].value]
        if(el.value == '' && el.lock!=1){
            found = i
            break;
        }
    }
    return found

}
var findNextUnAssigned = function(){
    bvnSelector.selectedIndex
    var unassigned = -1
    unassigned = findNextUnAssignedLoop(bvnSelector.selectedIndex)
    if(unassigned == -1){
        unassigned = findNextUnAssignedLoop(0)
    }
    if(unassigned != -1){
        bvnSelector.selectedIndex = unassigned
        setFieldValuesFromSelector()
    }
}

var setFieldValuesFromSelector = function(){
    var selected = currentSelectedVariable()
    selectedField=''
    setVariableField('selected_var_value',selected.value)
    if(selected.value != '' && selected.value != 0){
      bvnSelector.options[bvnSelector.selectedIndex].style.backgroundColor='green'
    } else {
      bvnSelector.options[bvnSelector.selectedIndex].style.backgroundColor=''
    }
    setVariableField('selected_var_source_file', selected.filename)
    setVariableField('selected_var_source_col', selected.column)
    setVariableField('selected_var_source_row', selected.row)
    setLock(selected.locked)
    localStorage.setItem('currentVariableAssignmentIdx', bvnSelector.options[bvnSelector.selectedIndex].value)
}
var clearFieldValueFromField = function(field){

}
var setFieldValueFromField = function(field){
 	selectedField = field.id
 	localStorage.setItem('currentFieldAssigmentId', field.id)
}

var setFieldValueFromUpdate = function(field_id, value){
  document.getElementById(field_id).value = value
  selectedField = ''
  localStorage.setItem('currentFieldAssigmentId', '')
}

var removeSurveyOverride = function(){
  import_survy_cnt-=1
  var element = document.getElementById('main_content_table')
  element.removeChild(element.childNodes[import_survy_cnt])
}

var buildSurveyOverride = function(){
	var add_survey_button = ""
	var header = ""
	if(import_survy_cnt == 0){
	  header = "<tr><td colspan='4'><select><option>New Survey(s)</option></select></td></tr><tr><td>MD</td><td>Inc</td><td>AZM</td></tr>"
	  add_survey_button = "<td><button onclick='buildSurveyOverride()'>+</button></td>"
	} else {
	  add_survey_button = "<td><button onclick='removeSurveyOverride()'>&nbsp;-</button></td>"
	}
	var surveyOverrideRow = document.createElement("TR")
  	var surveyOverrideCol = document.createElement("TD")
  	html = "<table>"+
  			header+       
  	       "<tr><td><input onfocus='setFieldValueFromField(this)' onblur='clearFieldValueFromField(this)' type='text' id='log_survey_md_"+import_survy_cnt+"' size=15></td>"+
  	       "<td><input onfocus='setFieldValueFromField(this)' onblur='clearFieldValueFromField(this)'  id='log_survey_inc_"+import_survy_cnt+"' type='text' size=15></td>"+
  	       "<td><input onfocus='setFieldValueFromField(this)' onblur='clearFieldValueFromField(this)'  id='log_survey_azm_"+import_survy_cnt+"' type='text' size=15></td>"+
  	       add_survey_button+"</tr>"+
  	       "</table>";
  	surveyOverrideCol.colSpan=3      
  	surveyOverrideCol.innerHTML = html
  	surveyOverrideRow.appendChild(surveyOverrideCol)
  	var element = document.getElementById('main_content_table')
  	element.insertBefore(surveyOverrideRow, element.childNodes[import_survy_cnt])
  	import_survy_cnt+=1
}
var buildVarNameSelector = function(){
    var assignables = sharedVars['selectable_definitions']
    var selector = document.createElement("SELECT");
    for(var i = 0; i < assignables.length; i++){
		var selectable =  sharedVars['selectable_definitions'][i]
		<?php if($fromtiein){?>

			if(selectable.db_table != 'welllog') {
				continue;
			}		
		<?php } ?>
      	var opt = document.createElement("option");
        opt.text = assignables[i].display_name
        opt.value = i
        if(selectable.value != '' && selectable.value!=0){
			opt.style.backgroundColor='green'
        }
        selector.id = 'var_name_selector_actual'
        selector.onchange = setFieldValuesFromSelector
        selector.onfocus = function(){selectedField=''}
        selector.options.add(opt,selector.options.length);
    }
    document.getElementById('var_name_selector').appendChild(selector)
    return selector 
} 

var wellLogPost = function(){
  
}

<?php if($fromtiein){?>
	buildSurveyOverride();
<?php } ?>

var bvnSelector = buildVarNameSelector()
var selectedField = ''
setFieldValuesFromSelector()
window.addEventListener("storage", function(e){
    var dataObj = JSON.parse(e.newValue)
    if(e.key != 'currentFieldAssigmentId'){
    	var selected = sharedVars['selectable_definitions'][bvnSelector.options[bvnSelector.selectedIndex].value]
    	buildDataDefinationFromLocalStorage(selected, dataObj)
    	setFieldValuesFromSelector()
    } else if(e.key == 'currentFieldAssigmentId') {
      	setFieldValueFromUpdate(selectedField, e.newValue)
    }
}, false)
</script>