<?php

require_once("witsml_encode.php");
class Survey {
    function __construct($request) {
    	$this->db_name = $request['seldbname'];
    	$this->db=new dbio("$this->db_name"); 
    }
    
    function get_bitProjection($rows){
    	$D2R = pi()/180;
    	$R2D=180.0/pi();
    	$this->db->OpenDb();
    	$this->db->DoQuery("Select projdip,bitoffset,propazm,pbmethod from wellinfo");
    	$wellinfo = $this->db->FetchRow();
    	$pa = floatval($wellinfo['propazm']);
    	if($pa>180)	$pa-=360; $pa*=$D2R;
    	$bitoffset = floatval($wellinfo['bitoffset']);
    	$r1 =$rows[0];
    	$md = floatval($r1['md'])+$bitoffset;
    	$pinc = $inc =floatval($r1['inc']);
    	$pazm = $azm = floatval($r1['azm']);
    	$ptvd =$tvd = floatval($r1['tvd']);
    	$pvs  = floatval($r1['vs']);
    	$pdl = $dl = floatval($r1['dl']);
    	$dip = floatval($wellinfo['projdip']);
    	$pmd = floatval($r1['md']);
    	$pns = floatval($r1['ns']);
    	$pew = floatval($r1['ew']);
    	$ptot= floatval($r1['tot']);
    	$pbot= floatval($r1['bot']);
    	$cl = $md - $pmd;
    	
    	$inc = $inc>180.0?$inc-360.0:$inc;
    	$pinc = $pinc>180.0?$pinc-360.0:$pinc;
    	$azm  = $azm>360.0?$azm-360.0:$azm;
    	$pazm = $pazm>360.0?$pazm-360.0:$pazm;
    	
    	if($inc<0.0||$inc>180.0) return $r1;
    	if($pinc<0.0||$pinc>180.0) return $r1;
    	$inc*=$D2R;
    	$azm*=$D2R;
    	$pinc*=$D2R;
    	$pazm*=$D2R;
    	$dl = acos(cos($pinc)*cos($inc))+(sin($pinc)*sin($inc)*cos($azm-$pazm));
    	$radius = $dl!=0.0?(2.0/$dl)*tan($dl/2.0):1.0;
    	$tvd = $ptvd+(($cl/2.0)*(cos($pinc)+cos($inc))*$radius);
    	$ns=$pns +( ($cl/2.0)* ((sin($pinc) * cos($pazm)) + (sin($inc) * cos($azm))) * $radius);
    	$ew=$pew + ( ($cl/2.0)* ((sin($pinc) * sin($pazm)) + (sin($inc) * sin($azm))) * $radius);
    	if (round($ns)!=0) $ca=atan2($ew,$ns);else $ca=(pi()*pi());
		if (round($ca,1)!=0.0) $cd=abs($ew/sin($ca));else $cd=$ns;
		$vs = $cd * cos($ca-$pa);
		$dl=(($dl*100)/$cl)*$R2D;
		$inc=$inc*$R2D;
		$azm=$azm*$R2D;
		$ca=$ca*$R2D;
		if($ca<0.0)	$ca+=360.0;
		$fault = 0.0;
		$tot=$ptot+(-tan($dip/57.29578)*abs($vs-$pvs));
		$bot=$pbot+(-tan($dip/57.29578)*abs($vs-$pvs));
		$tot+=$fault; $bot+=$fault;
		$pos=$tot-$tvd;
		$this->db->CloseDb();
   		return array('id'=>'0','proj'=>false,'isbit'=>true,'hide'=>0,
   		'method'=>$pa,'data'=>'','md'=>$md,'inc'=>$inc,
   		'azm'=>$azm,'tvd'=>$tvd,'vs'=>$vs,'ns'=>$ns,'ew'=>$ew,'ca'=>$ca,
   		'cd'=>$cd,'cl'=>$cl,'dl'=>$dl,'tot'=>$tot,'bot'=>$bot,'dip'=>$dip,'fault'=>$fault);
    	
    }
    function get_surveys($as='json',$order_by="ORDER BY id DESC"){
    	$this->db->OpenDb();
    	$this->db->DoQuery("SELECT * FROM surveys $order_by");
    	$rows =array();
    	while($row = $this->db->FetchRow()){
    		array_push($rows,$row);
    	}
    	$this->db->CloseDb();
    	if($as=='json'){
    		return json_encode($rows);
    	} else if ($as=='witsml'){
    		return witsml_encode($rows);
    	} else {
    		return $rows;
    	}
    	
    }
    function get_last_survey($as='json'){
    	$this->db->OpenDb();
    	$this->db->DoQuery("SELECT * FROM surveys ORDER BY md DESC limit 1;");
    	$rows =array();
    	while($row = $this->db->FetchRow()){
    		array_push($rows,$row);
    	}
    	$this->db->CloseDb();
    	if($as=='json'){
    		return json_encode($rows);
    	} else if ($as=='witsml'){
    		return witsml_encode($rows);
    	} else {
    		return $rows;
    	}

    }
    function get_projs($as='json'){
     	$this->db->OpenDb();
    	$this->db->DoQuery("SELECT * FROM projections ORDER BY id DESC;");
    	$rows =array();
    	while($row = $this->db->FetchRow()){
    		array_push($rows,$row);
    	}
    	$this->db->CloseDb();
    	if($as=='json'){
    		return json_encode($rows);
    	} else if ($as=='witsml'){
    		return witsml_encode($rows);
    	} else {
    		return $rows;
    	}
    	   	
    }
    
    
}
?>