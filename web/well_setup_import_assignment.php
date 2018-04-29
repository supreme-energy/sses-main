<?php 
include_once("sses_include.php");
require_once("dbio.class.php");
require_once("version.php");
$seldbname=$_REQUEST['seldbname'];

$db=new dbio($seldbname);
$db->OpenDb();
?>
<script>
<?php include "graph_partials/shared/assignment_definition_js_vars.php"?>
</script>
<table>
    <tr>
        <td>Name</td><td><div id='var_name_selector'></div></td>
    </tr>
    <tr>
        <td>Value</td><td><input type='text' id='selected_var_value'></td>
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
    <tr><td><button>clear selection</button></td><td><button>lock selection</button></td></tr>
    <tr>
        <td><button onclick='findNextUnAssigned()'>Next Unassigned</button></td>
    </tr>
</table>

<script>
var findNextUnAssignedLoop = function(startidx){
    var found = -1
    for(var i = startidx; i < bvnSelector.options.length; i ++){
        console.log(i)
        console.log(bvnSelector.options[i])
        if(i == parseInt(bvnSelector.selectedIndex)){ continue;}
        var el = sharedVars['selectable_definitions'][bvnSelector.options[i].value]
        console.log(el)
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
    var selected = sharedVars['selectable_definitions'][bvnSelector.options[bvnSelector.selectedIndex].value]
    var value_field = document.getElementById('selected_var_value')
    var source_file = document.getElementById('selected_var_source_file')
    var source_col = document.getElementById('selected_var_source_col')
    var source_row = document.getElementById('selected_var_source_row')

    value_field.value = selected.value
    source_file.value = selected.filename
    source_col.value  = selected.column
    source_row.value  = selected.row
    localStorage.setItem('currentVariableAssignmentIdx', bvnSelector.options[bvnSelector.selectedIndex].value)
}
var buildVarNameSelector = function(){
    var assignables = sharedVars['selectable_definitions']
    var selector = document.createElement("SELECT");
    for(var i = 0; i < assignables.length; i++){
        var opt = document.createElement("option");
        opt.text = assignables[i].display_name
        opt.value = i
        selector.id = 'var_name_selector_actual'
        selector.onchange = setFieldValuesFromSelector
        selector.options.add(opt,selector.options.length);
    }
    document.getElementById('var_name_selector').appendChild(selector)
    return selector 
} 



var bvnSelector = buildVarNameSelector()
setFieldValuesFromSelector()
window.addEventListener("storage", function(e){
    var dataObj = JSON.parse(e.newValue)
    var selected = sharedVars['selectable_definitions'][bvnSelector.options[bvnSelector.selectedIndex].value]
    buildDataDefinationFromLocalStorage(selected, dataObj)
    setFieldValuesFromSelector()
}, false)
</script>