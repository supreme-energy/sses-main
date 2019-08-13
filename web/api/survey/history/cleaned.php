<?php 
$db2=new dbio($seldbname);
$db2->OpenDb();
$db2->DoQuery("select * from deleted_survey_data where group_id = $group_id;");
$cldata = array();
while($row = $db2->FetchRow()) {
    $md=sprintf("%.2f", $row['md']);
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
	array_push($cldata,array(
        'md' => $md,
	    'inc' => $inc,
	    'azm' => $azm,
	    'tvd' => $tvd,
	    'ns' => $ns,
	    'ew' => $ew,
	    'vs' => $vs,
	    'dl' => $dl,
	    'cl' => $cl,
	    'dip' => $dip,
	    'fault' => $fault
	));
}
$md0 = $data[0]['md'];
$db2->DoQuery("select * from surveys where md < $md0 order by md limit 1");
$db2->FetchRow();
$last_md =  $db2->FetchField('md');
$data = array('cleaned_surveys' => $cldata, 'last_survey_depth' => $last_md);
$db2->CloseDb();

?>