<?
include("api_header.php");
include("readwellinfo.inc.php");
include("readappinfo.inc.php");
$id = $_REQUEST['id'];
$field_names = array('md', 'inc', 'azm', 'tvd', 'pos', 'vs', 'ns', 'ew', 'ca', 'cd', 'dl', 'cl', 'bot', 'dip', 'fault', 'method');
$updates_array = array();
$method_updated = false;
$query = "select * from projections where id = ". $id;
$db->DoQuery($query);
$db->FetchRow();
$data = $db->FetchField('data');
$pos = null;
foreach($field_names as $field_name){	
    if(isset($_REQUEST[$field_name])){
		$value = $_REQUEST[$field_name];
		if($field_name != 'pos'){
		  array_push($updates_array, "$field_name = '$value'");
		}
		$$field_name = $value;
    } else {
        if($field_name != 'pos'){
            $$field_name = $db->FetchField($field_name);
        } 
    }
}
if($pos===null){
    if ($method == 6 || $method == 7){ 
        $dexpl = explode(',',$data);
        $pos = $dexpl[2];
    } else if($method==8){
        $dexpl = explode(',',$data);
        $pos = $dexpl[1];
    }
    else { 
        $pos = 0;
    }
} 
$query = "select * from projections where id < ". $id. "order by id desc limit 1";
$db->DoQuery($query);
if($db->FetchRow()){
    $pmd = $db->FetchField('md');
    $pinc = $db->FetchField('inc');
    $pazm = $db->FetchField('azm');
    $ptvd = $db->FetchField('tvd');
    $pcd = $db->FetchField('cd');
    $pca = $db->FetchField('ca');
} else {
    $query = "select * from surveys where plan = 1";
    $db->DoQuery($query);
    $db->FetchRow();
    $pmd = $db->FetchField('md');
    $pinc = $db->FetchField('inc');
    $pazm = $db->FetchField('azm');
    $ptvd = $db->FetchField('tvd');
    $pcd = $db->FetchField('cd');
    $pca = $db->FetchField('ca');
    
}
$dmd=$md-$pmd;
$dinc=$inc-$pinc;
$dazm=$azm-$pazm;
$dtvd=$tvd-$ptvd;
$dcd=$cd-$pcd;
$dca=$ca-$pca;

if($method==0) $data="$dmd,0,0";
else if($method>=3 && $method<=5) $data="$dmd,$dinc,$dazm";
else if($method==6) $data="$tvd,$vs,$pos";
else if($method==7) $data="$tot,$vs,$pos";
else if($method==8) $data="$vs,$pos,$dip,$fault";
else $data="0,0,0";

if(count($updates_array) > 0 ){
	$query = "update projections set ". implode($updates_array, ',') . ", data='".$data."' where id=$id";
	$db->DoQuery($query);
}
if($autoposdec>0){
    $db2=new dbio($seldbname);
    $db2->OpenDb();
    $sql = "select (tot-tvd) as bprjtops from surveys where plan = 1;";
    $db->DoQuery($sql);
    $bprjtpos_r=$db->FetchRow();
    $sval = $bprjtpos_r['bprjtops'];
    if($sval>0){
        $svalsign='positive';
    } else{
        $svalsign='negative';
    }
    $decval= $autoposdec;
    if($svalsign=='negative') $decval=$decval*-1;
    $sql = "select * from projections order by md";
    $db->DoQuery($sql);
    while($r1 = $db->FetchRow()){
        $sval = $sval - $decval;
        if($db->FetchField('method')==8){
            $rowid = $db->FetchField('id');
            $data = $db->FetchField('data');
            $split = explode(',',$data);
            if($svalsign=='positive'){
                if($sval < 0) $sval = 0;
            } else{
                if($sval > 0) $sval = 0;
            }
            $split[1]=$sval;
            $ndata = implode(',',$split);
            $sql = "update projections set data='$ndata' where id=$rowid";
            $db2->DoQuery($sql);
        }
    }
    $db2->CloseDb();
}
exec("../sses_gva -d $seldbname ");
exec("../sses_cc -d $seldbname");
exec("../sses_cc -d $seldbname -p");
exec ("../sses_af -d $seldbname");
echo json_encode(array("status" => "Success", "message" => "operation completed"));
?>