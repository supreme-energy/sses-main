<?php 

function import_from_stored_file($sdepth, $edepth, $seldbname){
    $filename="/tmp/custom_import_$seldbname.las";
    $infile=fopen("$filename", "r");
    if(!$infile){
        echo json_encode(array("status" => "error", "message" => "upload file"));
        exit;
    }
    $tempfile=tmpfile();
    do {
        $line=fgets($infile,1024);
        if($line==FALSE){
            echo json_encode(array("status" => "error", "message" => "End of file looking for ~A data section"));
            exit;
        }
    } while(stristr($line, "~A")==FALSE);
    do {
        $line=fgets($infile,1024);
        if($line==FALSE){
            echo json_encode(array("status" => "error", "message" => "End of file looking for ~A data section"));
            exit;
        }
    } while(stristr($line, "~A")==FALSE);
    
    while($line=fgets($infile,1024)) {
        $line=StripExtraSpace($line);
        $line=Trim($line);
        if(strlen($line)>1) {
            fputs($tempfile, $line);
            fputs($tempfile, "\n");
        }
    }
    #$md = $data[0];
    #$val = $data[1];
    #$tvd = $data[4];
    #$vs  = $data[5];
    $columns = array(
        'ROP.ft/hr',
        'ARPM.RPM',
        'RPM_W.RPM',
        'DRPM.RPM',
        'WOB.kLb',
        'PRES.psi',
        'TORQ.ft-lbs',
        'DIFP.psi',
        'GPM.GPM',
        'HKLA.kLb');
    fclose($infile);
    fseek($tempfile,0);
    fclose($tempfile);    
}
?>