<?php
	
	$client = new SoapClient(
		null,
		array(
			'soap_version' => SOAP_1_2,
			'location' => 'https://hess1.petrolink.net/petrovault_hess1/witsml/wmls.asmx',
			'login'=>'WW_HessSSESTEST',
			'password'=>'xr6cYEfbzx'
			
		)
			
		
	)
?>