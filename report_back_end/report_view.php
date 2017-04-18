<?
	require_once("dbio.class.php");
	require_once("classes/Reports.php");
	if(!$reports_loader){
		$report_loader = new Reports($_REQUEST);
	}
	$ja = $_REQUEST['ja'];
	$id = $_REQUEST['id'];
	$new= $_REQUEST['new'];
	$data = $report_loader->report_list($_REQUEST);
?>
<button id='exit_button' style="position:absolute;" onclick="window.location='index.php?cmd=report_list&ja=<? echo $ja?>'">Exit Report Mode</button>
<div id ='new_rep' style="position:absolute;display:<?echo isset($_REQUEST['new'])?'block':'none'?>;"><button onclick="$('#new_rep').hide();alarm.pause();new_report_ack=true;" style="font-size:27;">NEW REPORT ACKNOWLEDGED</button></div>
<? if($id){?>
	<img id='report_image' src="report_image.php?ja=<?echo $ja?>&id=<?echo $id?>">
<? }else{?>
	<h1 style='padding-left:140px'>No Reports Available</h1>
<?}?>
<script>
	var w=window.innerWidth;
	var h=window.innerHeight;
	$( "#report_image" ).height(h-30).width(w-30);
	$( "#report_image" ).css({'background-color':'white'});
	$("#new_rep").css({left:(w/2)-200});
	var current_count = <?=count($data->results)?>;
	var outstanding_request = false;
	var alarm = new Audio("ALARME2.WAV");
	var play_alarm = function(){
		if(!new_report_ack){
			alarm.play();
			setTimeout(play_alarm,3000);
		}
	}
	var new_report_ack=true;
	<?if(isset($_REQUEST['new'])){?>
		var new_report_ack=false;
	<?}?>
	play_alarm();
	var check_for_new_report = function(){
			
		if(!outstanding_request){
			outstanding_request=true;
			$.ajax({
				url: "report_count.php?cmd=report_count&ja=<?=$_REQUEST['ja']?>",
				cache:false,
				complete:function(){
					outstanding_request=false;
				},
				success:function(data){
					r = eval('('+data+')');
					if(r.count != current_count){
						clearInterval();
						window.location = "load_latest_report.php?ja=<?=$_REQUEST['ja']?>&new=1";
					}
				}
			})
			}
		}
		setInterval('check_for_new_report()', 3000);
		$(window).resize(function(){
				var w=window.innerWidth;
				var h=window.innerHeight;
				$( "#report_image" ).height(h-30).width(w-30);
				$( "#report_image" ).css({'background-color':'white'});
		})
</script>