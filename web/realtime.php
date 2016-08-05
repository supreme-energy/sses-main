<?php
/*
 * Created on Jul 2, 2016
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("dbio.class.php");
if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : '');
$db= new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("select * from wellinfo");
$ghoston=$db->FetchField("rt_ghost_stream");
println("ghost on value :".$ghoston);
	try{
	require 'HTTP/Upload.php';
	$upload = new http_upload('en');
	$file = $upload->getFiles('userfile');
	if (PEAR::isError($file)) {
		
	} else{
		if ($file->isValid()) {
			$file->setName('uniq');
			$dest_dir = './tmp/';
			$dest_name = $file->moveTo($dest_dir);
			if (PEAR::isError($dest_name)) {
				die ($dest_name->getMessage());
			}
			$filename=sprintf("$dest_dir%s", $file->getProp('name'));
			$temp=tmpfile();
			$infile=fopen("$filename", "r");
			if(!$infile)	die("<pre>File not found: $filename\n</pre>");
			do{
				$line=fgets($infile,1024);
				if($line==FALSE) 
					die("End of file looking for curve section \n");
			} while(stristr($line,"~Curve Information Section")==FALSE);
			$ar_cols=array();
			
			do {
				$line=fgets($infile);
				
				$ar = explode(":",$line);
				if(count($ar)>1){
					
					$ar2 = explode(" ",trim($ar[1]));
					$idxval_a = array_slice($ar2,0,1);
					$idxval=$idxval_a[0];
					$value_name = implode("",array_slice($ar2,1,count($ar2)));
					echo $value_name." : ".$idxval."<br>/n";
					
					$ar_cols[$value_name]=($idxval-1);
				}
			
				if($line==FALSE) 
					die("End of file looking for ~A data section\n");
			} while(stristr($line, "~A")==FALSE);
		//	print_r($ar_cols);
			while($line=fgets($infile)) {
				$line=trim($line);
				$line=preg_replace( '/\s+/', ',', $line );
				fputs($temp, "$line\n");
			}
			fclose($infile);
			fseek($temp,0);
			$sql_pa_ks = "select md,tvd,vs,plan from surveys order by id desc limit 2;";
			$db->DoQuery($sql_pa_ks);
			$pa=$db->FetchRow();
			$ks=$db->FetchRow();
			$mdks = $ks['md'];
			$vsks = $ks['vs'];
			$tvdks=$ks['tvd'];
			$mdpa = $pa['md'];
			$vspa = $pa['vs'];
			$tvdpa = $pa['tvd'];
			while (($data = fgetcsv($temp, 5000, ",")) !== FALSE) {
				
				$md=$data[$ar_cols["Depth"]];
				$val=$data[$ar_cols["Gamma"]];
				$tvd=(($md-$mdks)*($tvdpa-$tvdks)/($mdpa-$mdks))+$tvdks;
				$vs=(($md-$mdks)*($vspa-$vsks)/($mdpa-$mdks))+$vsks;
				if($md=="")	$md=0;
				if($tvd=="")	$tvd=0;
				if($vs=="")	$vs=0;
				if($value=="")	$value=0;
				$sql = "INSERT INTO  ghost_data (md,value,tvd,vs,depth) VALUES ($md,$val,$tvd,$vs,$md);";
				echo $sql."<br>";
				$result=$db->DoQuery($sql);
			}
		} elseif ($file->isMissing()) {
			
		} elseif ($file->isError()) {
			echo '<pre>';
			echo $file->errorMsg() . "\n";
			echo '</pre>';
			
		}
			
	
	}
	}catch(Exception $er){}
?>
<!doctype html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="gva_tab8.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<title><?php echo $dbrealname ?>-SGTA Editor<?php echo " ($seldbname)"; ?></title>
<style>
#clickimage[src=''] {
	display:none;
}
</style>
<script src='//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js'></script>
<script language='javascript' type='text/javascript' src='waitdlg.js'></script>

<script>
var current_rt_depth = 0;
var stream_dp_count=0;
var current_rt_id = 0;
var my_dbname = '<?php echo $seldbname ?>';
function streamPolling(){
	if($('#pause-stream').is(":visible")){	
		 $.getJSON('/sses/json/getghostgamma.php',
		 "seldbname=<?=$seldbname?>&depth="+current_rt_id,
		function(data) {
			if(data.res == 'ERR') alert('ERROR: ' + data.msg);
			$.each( data, function( key, val ) {
	    		current_rt_id=val.id
	    		stream_dp_count=stream_dp_count+1;
	    		$("#streaming-data-table tr:first").after('<tr class="surveys"><td class="surveys">'+val.tvd+'</td><td class="surveys">'+val.md+'</td><td class="surveys">'+val.value+'</td></tr>');
	 		 });
	 		 $("#streaming-data-count").text(stream_dp_count);
		}).fail(function( jqxhr, textStatus, error ) {
			alert('Request Failed: ' + textStatus + ", " + error);
		});
	}
	//setTimeout(streamPolling,10000);
}
setTimeout(streamPolling, 1000);
$(document).ready(function() {
	
	$('#pause-stream').click(function(){
		$('#pause-stream').hide()
		$('#play-stream').show()
		$.getJSON("/sses/json/rt_stream_pause.php","seldbname=<?php echo $seldbname; ?>",function(data){
			
		});
	})
	$('#play-stream').click(function(){
		$('#play-stream').hide()
		$('#pause-stream').show()
		$('#disable-ghost').hide()
		$('#enable-ghost').show()
		$('#streaming-data').show()
		$.getJSON("/sses/json/rt_disable_ghost.php","seldbname=<?php echo $seldbname; ?>",function(data){});
		$.getJSON("/sses/json/rt_stream_play.php","seldbname=<?php echo $seldbname; ?>",function(data){});
	})
	$('#enable-ghost').click(function(){
		$('#streaming-data').hide()
		$('#pause-stream').hide()
		$('#play-stream').show()
		$('#enable-ghost').hide()
		$('#disable-ghost').show()	
		
		$.getJSON("/sses/json/rt_stream_pause.php","seldbname=<?php echo $seldbname; ?>",function(data){});
		console.log("execution");
		$.getJSON("/sses/json/rt_enable_ghost.php","seldbname=<?php echo $seldbname; ?>",function(data){
			console.log("rt_enable_ghost completed");
			if(window.opener && !window.opener.closed){
//				window.opener.callMask(true);
				window.opener.location.reload()
			}
		}).done(function() {
    		console.log( "second success" );
 		 });
 		
	})
	$('#disable-ghost').click(function(){
		$('#streaming-data').show()
		$('#disable-ghost').hide()	
		$('#enable-ghost').show()
		$.getJSON("/sses/json/rt_disable_ghost.php","seldbname=<?php echo $seldbname; ?>",function(data){
			if(window.opener && !window.opener.closed){
//				window.opener.callMask(true);
				window.opener.location.reload()
			}
		});	
	})
});
</script>
</head>
<body>
	<div id="sgta-rt-controls-div">
	<div class='settings'>
		<table>
			<tr><td>
					<div id="pause-stream" style="float:left">
					<button>
					<div><img width=18 height=18 src="/sses/imgs/pause.png"></div>
					<div>Pause</div>
					</button> 
					</div>
					<div id="play-stream" style="display:none;float:left">
					<button>
					<div><img width=18 height=18 src="/sses/imgs/play.png"></div>
					<div>Play</div>
					</button> 
					</div></td><td>
					<div  id="enable-ghost" style="display:<?php if($ghoston){echo "none";} else{echo "block";}?>;float:left;position:relative;cursor:pointer">
					<button>
					<div style="position:relative;"><img width=18 height=18 src="/sses/imgs/ghost.png"></div>
					<div>Enable Ghost</div> 
					</div>
					</button>
					<div  id="disable-ghost" style="display:<?php if($ghoston){echo "block";} else{echo "none";}?>;float:left;position:relative;cursor:pointer">
					<button>
					<div style="position:relative;"><img width=18 height=18 src="/sses/imgs/ghost.png"></div>
					<div style="position:absolute;top:2px;left:40px;"><img width=18 height=18 src="/sses/imgs/cancel.png"></div>
					<div>Disable Ghost</div> 
					</div>
					</button>
					
			</td></tr>
			<tr><td><div>Manual Gamma Import</div>
				<div>
						<FORM method="post" enctype="multipart/form-data">
		<b>File to import from:</b>
		<br>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
		<INPUT type="file" name="userfile" size="70">
		<INPUT type="submit" value="Import">
		</form>
				</div>
				
			</td></tr>
			<tr><td>data points:</td><td><div id="streaming-data-count"></div></td></tr>
			<tr id="streaming-data"><td colspan=2><div style="height:550px;overflow:scroll;width:300px">
				<table id="streaming-data-table" style="width:280px;"  class='surveys'>
					<tr class='surveys'><th class='surveys'>TVD</th><th class='surveys'>MD</th><th class='surveys'>GAMMA</th></tr>
				</table>
			</div></td></tr>
		</table>
	
	</div>
	<br>
	</div>
</body>
</html>