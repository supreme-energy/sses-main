<?php /*
	Written by: Mark Carrier
	Copyright: 2011, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
require_once("dbio.class.php");
require_once("tabs.php");
require_once("login.class.php");
include_once("sses_include.php");

$currtab=$_GET['currtab'];
if("$currtab"=="")  $currtab=$_POST['currtab'];

$entity_id = $_SESSION['entity_id'];

$userids=array();
$usernames=array();
$plevels=array();

$db=new dbio("sgta_index");
$db->OpenDb();

$db->DoQuery("SELECT * FROM users WHERE entity_id = '$entity_id';");
while($db->FetchRow()) {
	$useridx= $db->FetchField("id");
	$userids[]= $useridx;
	$usernamex = $db->FetchField("email");
	$usernames[] = $usernamex;
	$plevelx = $db->FetchField("plevel");
	$plevels[] = $plevelx;
} 

$db->DoQuery("SELECT * FROM entities WHERE id = '$entity_id';");
if($db->FetchRow())
	$entityname=$db->FetchField("entity_name");
else
	$entityname="UNKNOWN";

$db->CloseDb();
?>
<HTML>
<HEAD>
<link rel="stylesheet" type="text/css" href="gva_admin.css" />
<link rel="stylesheet" type="text/css" href="tabs.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<title><?echo "$entityname";?>- Administration</title>
</HEAD>
<BODY>
<?
$maintab=8;
include "apptabs.inc.php";
include("waitdlg.html");
?>
<TABLE class='tabcontainer'>
<TR>
<TD>
	<TABLE style='margin: 2 0; padding: 0 0;' class='container'>
	<TR>
	<TD>
		<img src='digital_tools_logo.png' width='76' height='74' align='left'>
	</TD>
	<TD>
		<H2 style='line-height: 1.0; font-style: italic; color: #040;'>Supreme Source Energy Services, Inc.</H2>
		<H1 style='line-height: 0.3;'>Subsurface Geological Tracking Analysis</H1>
		Version 2.4.1 (<?echo $_SERVER['SERVER_NAME']?>:<?echo $_SERVER['SERVER_PORT']?> - <?echo $hostname?>)
	</TD>
	<TD>
		<img src='Geology.gif' align='right' style='border: 2px solid #080; border-style: inset; padding: 0px 0px; width: 126; height: 74;'>
	</TD>
	</TR>
	</TABLE>

	<TABLE class='container'>
	<TR>
	<TD style='text-align: center;'>
		<big><?echo $entityname?></big>
		<br>
		<big>ADMINISTRATION</big>
	</TD>
	</TR>
	</TABLE>

<?$tabs = new tabs("Configuration");
	$tabs->start("Add User"); ?>
	<center>
	<h3>ADD USER: <font color="FF0000"> 
		<? if ($currtab==1) {
			   if ($_GET['login_err'] == 0) 
		           echo ""; 
		       else {
		   	       echo $_SESSION['err_login_msg'];
		   	       $_SESSION['err_login_msg']= "";
		       }
		   }	   		
		 ?></font></h3>
	</center>
	<TABLE class='container2'>
	<TR>
	    <FORM style="padding: 4 0;" method="post" action="useradd.php">
        <input type="hidden" name="currtab" value="1">
        <TR>
	       <TD class='container' style='text-indent:170px;'>
		     New User Login Email: 
		     <input type="text" size="30" text-align="left" name="newlogin" value="<? echo "$newlogin"?>"><br>
		   </TD>
		</TR>
		<br>
		<TR>
		   <TD class='container' style='text-indent:240px;'>
		     Password: 
		     <input type="password" size="30" name="newpassword1" value="<? echo "$newpassword1"?>"><br>
		  </TD>
		</TR>
		<br>
		<TR>
		   <TD class='container' style='text-indent:190px;'>
             Re-Type Password:
             <input type="password" size="30" name="newpassword2" value="<? echo "$newpassword2"?>"><br>
		   </TD>
		</TR>
		<br>
		<TR>
		   <TD class='container' style='text-indent:220px;'>
		     Privilege Level: 
		     <select size="1" style="font-size: 10pt;" id='selplevel' name='selplevel' ONCHANGE="OnChangePLevel(this.form)">
			    <option value="READ_ONLY">READ_ONLY</option>
			    <option value="READ_WRITE">READ_WRITE</option>
		     </select>
		   </TD>
		</TR>
		<br>
		<br>
	    <TR>
	       <TD style='text-align:center;'>
		      <center>
		      <input type="Submit" value="Save User">
		      </center>
	       </TD>
	    </TR>
	    </FORM>
	</TR>
	</TABLE>
	<?
	$tabs->end();
	$tabs->start("Modify User");  
	$pwdmod="false"; 
    ?>
	<center>
	<h3>MODIFY USER: <font color="FF0000"> 
		<?if ($currtab==2) {
			   if ($_GET['login_err'] == 0) 
		           echo ""; 
		       else {
		   	       echo $_SESSION['err_login_msg'];
		   	       $_SESSION['err_login_msg']= "";
		       }
		  }	      		
		?></font></h3>
	</center>
	<TABLE class='container2'>
	<TR>
	   <FORM style="padding: 4 0;" method="post" action="usermod.php">
	   <input type="hidden" name="currtab" value="2">
	   <input type="hidden" name="userid" value="<?echo "$userid";?>">
	   <TR>
          <TD class='container' style='text-indent:290px;'>
		      Select User:
              <select style='font-size: 10pt;' name='selusermod' ONCHANGE="OnChangeUserModify(this.form)"> 
		      <? $cnt = count($usernames);
		         for($i=0; $i<$cnt; $i++) {
			         echo "<option value='$usernames[$i]'";
			         if($selusermod==$usernames[$i])  {
			      	     echo " selected='selected'";
			      	     $userid = $userids[$i];
			      	     $selplevel = $plevels[$i];
			         }
			         echo ">$usernames[$i]</option>";
		         }
		      ?>
		     </select>
		  </TD>
       </TR>
	   <br><br>
	   <TR>
		  <TD class='container' style='text-indent:260px;'>
		     <label style="text-indent:15px;" for="new">New Email Login: </label>
		     <INPUT type="text" name="newemaillogin" value="<?echo "$selusermod";?>">
		  </TD>
	   </TR>
	   <TR>
		  <TD class='container' style='text-indent:270px;'>
		      <label style="text-indent:20px;" for="newpassword1">New Password: </label>
		      <INPUT type="password" name="newpassword1"  autocomplete="off">
		  </TD>
	   </TR>
	   <TR>
		   <TD class='container' style='text-indent:220px;'>
		      <label style="text-indent:15px;" for="newpassword2">Re-type New Password: </label>
		      <INPUT type="password" name="newpassword2" autocomplete="off">
		  </TD>
	   </TR>
	   <TR>
		   <TD class='container' style='text-indent:245px;'>
    	      <label style="text-indent:10px;" for="selplevel">New Privilege Level: </label>
		      <SELECT size="1" style="font-size: 10pt;" name="selplevel" value="<?echo "$selplevel";?>" ONCHANGE="OnChangePLevel(this.form)">
		          <option value="No_Change">No Change</option>
		          <option value="READ_ONLY">READ_ONLY</option>
		          <option value="READ_WRITE">READ_WRITE</option>
		          <option value="ADMIN">ADMIN</option>
		      </SELECT>
	      </TD>
	   </TR>
	   <TR>
	       <TD class='container'>
	       	  <center>
		      <input type="Submit" value="Modify User">
		      </center>
          </TD>
       </TR>
       </FORM>
    </TR>
	</TABLE>
	<?
	$tabs->end();
	$tabs->start("Delete User"); ?>
	<center>
	<h3>DELETE USER: <font color="FF0000"> 
	    <? if ($currtab==3) {
			   if ($_GET['login_err'] == 0) 
		           echo ""; 
		       else {
		   	       echo $_SESSION['err_login_msg'];
		   	       $_SESSION['err_login_msg']= "";
		       }
		   }	   		
		?></font></h3>
	</center>
   	<TABLE class='container2'>
   	<TR>  
   	    <FORM style="padding: 4 0;" method="post" action="userdel.php">
   	    <input type="hidden" name="currtab" value="3">
   	    <input type="hidden" name="userid" value="<?echo "$userid";?>"
        <TR>
           <TD class='container' style='text-align:center;'>
               Select User:
		       <SELECT style='font-size: 10pt;' name='seluserdel' ONCHANGE="OnChangeUserDelete(this.form)" >  
		        <? $cnt = count($usernames);
		           for($i=0; $i<$cnt; $i++) {
			           echo "<option value='$usernames[$i]'";
			           if($seluserdel==$usernames[$i])	{
			      	       echo " selected='selected'";
			      	       $userid = $userids[$i];
			           }
			           echo ">$usernames[$i]</option>";
		           }
		        ?>
		       </SELECT>
		   </TD>
	    </TR>
		<br><br><br>
		<TR>
	       <TD class='container'>
	       	  <center>
		      <input type="Submit" value="Delete User">
		      </center>
	       </TD>
	    </TR>
	    </FORM>
	</TR>
	</TABLE>
	<?
	$tabs->end();

	if($currtab==1) $tabs->active="Add User";
	if($currtab==2) $tabs->active="Modify User";
	if($currtab==3) $tabs->active="Delete User";
	$tabs->run();
	?>
	<br><center><small><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></small></center>
</TD>
</TR>
</TABLE>
</BODY>
<script language="javascript" type="text/javascript" src="datetimepicker.js"></script>
<script language="javascript" type="text/javascript" src="waitdlg.js"></script>
<SCRIPT language="javascript">

function OnChangeUserModify(rowform) {
	t = 'gva_admin.php?selusermod=rowform.selusermod.value';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}

function OnChangeUserDelete(rowform) {
	t = 'gva_admin.php?seluserdel=rowform.selusermod.value';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}

function OnChangePLevel(rowform) {
	t = 'gva_admin.php?selplevel=rowform.selplevel.value';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}

</HTML>
