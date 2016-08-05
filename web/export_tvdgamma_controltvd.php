<?php
/*
 * Created on Dec 15, 2015
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("dbio.class.php");
 $filename = $dbname."_control_tvd_gamma_md_export.csv";
 header('Content-type: text/csv');
 header("Content-disposition: attachment;filename=$filename");

 $seldbname = $_GET['seldbname'];
 // value of control log points
 
 $tvd_g1=7861;
 $gamma_g1=108.0551;
 
 $tvd_g2=7861.5;
 $gamma_g2=94.5527;
 
 
 //load well logs
 $sql = "select * from welllogs";
 $db=new dbio($seldbname);
 $db->OpenDb();
 $db2 = new dbio($seldbname);
 $db2->OpenDb();
 $db->DoQuery($sql);
 $db3 = new dbio($seldbname);
 $db3->OpenDb();
 $control_log="select * from controllogs limit 1";
 $db3->DoQuery($control_log);
 $db3->FetchRow();
 $cl_tn = $db3->FetchField("tablename");
 echo("md,gamma,tvd control,gamma control\n");
 while($db->FetchRow()){
	 $scale =$db->FetchField("scalefactor");
	 $bias = $db->FetchField("biasfactor");
	 $tablename = $db->FetchField("tablename");
	 $sql2= "select * from $tablename";
	 $db2->DoQuery($sql2);
	 while($db2->FetchRow()){
		 $md = $db2->FetchField("md");
		 $tvd_p1 = $db2->FetchField("depth");
		 $gamma_p1 = ($db2->FetchField("value")*$scale+$bias);
		 
		 $tvd_p2 = $tvd_p1;
		 $gamma_p2= 0;
		 
		 $sql_pu = "select * from $cl_tn where md < $tvd_p1 order by md desc limit 1";
		 $db3->DoQuery($sql_pu);
		 $db3->FetchRow();
		 $tvd_g1 = $db3->FetchField("md");
		 $gamma_g1= $db3->FetchField("value");
		 $sql_pd = "select * from $cl_tn where md > $tvd_p1 limit 1";
		 $db3->DoQuery($sql_pd);
		 $db3->FetchRow();
		 $tvd_g2 = $db3->FetchField("md");
		 $gamma_g2= $db3->FetchField("value");
		 if($tvd_g2==$tvd_g1){
		 	echo "point equality<br>";
		 	$sql_pd = "select * from $cl_tn where md > $tvd_g2 limit 1";
		 	$db3->DoQuery($sql_pd);
		 	$db3->FetchRow();
		 	$tvd_g2 = $db3->FetchField("md");
		 	$gamma_g2= $db3->FetchField("value");
		 }
		//echo "$tvd_p1,$gamma_p1<br>";
		//echo "$tvd_p2,$gamma_p2<br>";
		//echo "$tvd_g1,$gamma_g1<br>";
		//echo "$tvd_g2,$gamma_g2<br>"; 
		$x12 = $tvd_g1 - $tvd_g2;
		$x34 = $tvd_p1 - $tvd_p2;
		$y12 = $gamma_g1 - $gamma_g2;
		$y34 = $gamma_p1 - $gamma_p2;
		
		$c = $x12 * $y34 - $y12 *$x34;
		
		
		  // Intersection
		$a = $tvd_g1 * $gamma_g2 - $gamma_g1 * $tvd_g2;
		$b = $tvd_p1 * $gamma_p2 - $gamma_p1 *  $tvd_p2;
		
		$x = ($a * $x34 - $b * $x12) / $c;
		$y = ($a * $y34 - $b * $y12) / $c;
		if($gamma_p1!=0){
			echo "$md,$gamma_p1,$x,$y\n";
		}
	}
 }
 
 
?>

