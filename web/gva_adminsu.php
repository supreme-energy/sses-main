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

$currtab = $_GET['currtab'];
if("$currtab"=="")  $currtab=$_POST['currtab'];

$entity_id = $_SESSION['entity_id'];

$newlogin = "";
$newpassword1 = "";

$usernames=array();
$entities=array();

$db=new dbio("sgta_index");
$db->OpenDb();

$db->DoQuery("SELECT * FROM entities ORDER BY entity_name;");
while($db->FetchRow()) {
    $entitynamex=$db->FetchField("entity_name");
    $entities[]=$entitynamex;
}

$db->DoQuery("SELECT * FROM users ORDER BY email;");
while($db->FetchRow()) {
    $usernamex= $db->FetchField("email");
    $usernames[]= $usernamex;
} 


$db->CloseDb();
?>
<HTML>
<HEAD>
<link rel="stylesheet" type="text/css" href="gva_admin.css" />
<link rel="stylesheet" type="text/css" href="tabs.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<title><?echo "$entityname";?>Administration</title>
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
		<img src='logo.gif' width='76' height='74' align='left'>
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
	$tabs->start("Add User"); 
      ?>
	<center>
	<h3>ADD USER: <font color="FF0000"> 
            <? if ($currtab==1) {
                   if($_GET['login_err']==0) 
                       echo ""; 
                   else {
	   	       echo $_SESSION['err_login_msg'];
                       $_SESSION['err_login_msg'] = "";
                   }
               }	   		
            ?></font></h3>
	</center>
        <TABLE>
        <TR>
           <TD style="text-indent:300px;" >
           <FORM style="padding: 4 0;" method="get" action="adm_adduser.php">
	    <big>Select Entity: </big>
	    <select size="1" style="font-size:11pt;" name="selentityname" ONCHANGE="OnChangeEntity(this.form)">
               <? $cnt = count($entities);
	            for($i=0; $i<$cnt; $i++) {
                        echo "<option value='$entities[$i]'";
                        if($selentityname==$entities[$i])  {
                            echo " selected='selected'";

                        }
                        echo ">$entities[$i]</option>";
                    }
                ?>
            </select>
           </FORM>
           </TD>
        </TR>
	<TR>
           <TD>
           <FORM style="padding: 4 0;" method="post" action="adm_adduser.php">
           <input type="hidden" name="currtab" value="1">
	    <TR>
               <TD style='text-indent:185px;'>
		   New User Login Email: 
		   <input type="text" size="30" style="text-align:left;" name="newlogin" value="<?echo "type new email login here"?>">
               </TD>
            </TR>
            <TR>
               <TD style='text-indent:258px;'>
		   Password: 
		   <input type="password" size="30" name="newpassword1"  value="<?echo "$newpassword1"?>">
               </TD>
            </TR>
            <TR>
               <TD style='text-indent:204px;'>
                   Re-Type Password: 
                   <input type="password" size="30" name="newpassword2" value="<? echo "$newpassword2"?>">
               </TD>
            </TR>
            <TR>
               <TD style='text-indent:231px;'>
		   Privilege Level: 
		   <select size="1" style="font-size:9pt;" name="selplevel" ONCHANGE="OnChangePLevel(this.form)">
                       <option value="READ_ONLY">READ_ONLY</option>
                       <option value="READ_WRITE">READ_WRITE</option>
                       <option value="ADMIN">ADMIN</option>
		   </select>
               </TD>
	    </TR>
	    <TR>
 	       <TD style='text-indent:415px;'>
	           <input type="Submit" value="Save User">
	       </TD>
	    </TR>

	</FORM>
        </TD>
        </TR>
	</TABLE>
      <?
	$tabs->end();
	$tabs->start("Modify User");  
      ?>
	<center>
	<h3>MODIFY USER: <font color="FF0000"> 
		<?if($currtab==2) {
                      if ($_GET['login_err'] == 0) {
	    	          echo ""; 
                      } else {
		          echo $_SESSION['err_login_msg'];
                          $_SESSION['err_login_msg'] == "";
                      }
		  }	   		
		?></font></h3>
	</center>
        <TABLE>
        <TR>
           <TD style="text-indent:300px;" >
           <FORM style="padding: 4 0;" method="get" action="adm_adduser.php">
	    <big>Select Entity: </big>
	    <select size="1" style="font-size:11pt;" name="selentityname" ONCHANGE="OnChangeEntity(this.form)">
               <? $cnt = count($entities);
	            for($i=0; $i<$cnt; $i++) {
                        echo "<option value='$entities[$i]'";
                        if($selentityname==$entities[$i])  {
                            echo " selected='selected'";

                        }
                        echo ">$entities[$i]</option>";
                    }
                ?>
            </select>
           </FORM>
           </TD>
        </TR>
        <TR>
	   <FORM style="padding: 4 0;" method="post" action="adm_moduser.php">
           <input type="hidden" name="currtab" value="2">
           <TR>
              <TD style="text-indent:230px";>
               Select User to Modify:
              <select style="font-size: 10pt;" name="selusermod" ONCHANGE="OnChangeUserModify(this.form)" > 
              <? $cnt = count($usernames);
	         for($i=0; $i<$cnt; $i++) {
                     echo "<option value='$usernames[$i]'";
                     if($selusermod==$usernames[$i])  {
                         echo " selected='selected'";
                     }
                     echo ">$usernames[$i]</option>";
		 }
              ?>
              </select>
              </TD>
           </TR>
           <TR>
              <TD style="text-indent:140px;">
               New Password (blank, if no change) : </label>
               <input type="password" name="newpassword1" autocomplete="off" >
              </TD>
           </TR>
           <TR>
              <TD style="text-indent:208px;">
               Re-Type New Password :
               <input type="password" name="newpassword2" autocomplete="off" >
	      </TD>
           </TR>
           <TR>
	      <TD style="text-indent:268px;">
    	       Privilege Level : </label>
               <SELECT size="1" style="font-size:9pt;" name="selplevel" ONCHANGE="OnChangePLevel(this.form)">
		    <option value="No Change">No Change</option>
		    <option value="READ_ONLY">READ_ONLY</option>
		    <option value="READ_WRITE">READ_WRITE</option>
		    <option value="ADMIN">ADMIN</option>
               </SELECT>
              </TD>
           </TR>
           <TR>
	      <TD style='text-indent:400px;'>
               <input type="Submit" value="Save Changes" >
              </TD>
           </TR>
           </FORM>
        </TR>
        </TABLE>
	<?
	 $tabs->end();
	 $tabs->start("Delete User");
        ?> 
	<center>
	<h3>DELETE USER: <font color="FF0000"> 
	    <?if($currtab==3) {
	          if ($_GET['login_err'] == 0) {
	    	      echo ""; 
	          } else {
 	   	      echo $_SESSION['err_login_msg'];
                      $_SESSION['err_login_msg'] = "";
                  }
            }	   		
	    ?></font></h3>
	</center>
        <TABLE>
        <TR>
           <TD style="text-indent:300px;" >
           <FORM style="padding: 4 0;" method="post" action="adm_deluser.php">
           <input type="hidden" name="currtab" value="3">
	    <big>Select Entity: </big>
	    <select size="1" style="font-size:11pt;" name="selentityname" ONCHANGE="OnChangeEntity(this.form)">
               <? $cnt = count($entities);
	          for($i=0; $i<$cnt; $i++) {
                      echo "<option value='$entities[$i]'";
                      if($selentityname==$entities[$i])  {
                          echo " selected='selected'";
                      }
                      echo ">$entities[$i]</option>";
                  }
                ?>
            </select>
           </FORM>
           </TD>
        </TR>

	<TR>
           <TD style='text-indent:250px;'>
             Select User to Delete:
             <select style="font-size: 10pt;" name="seluserdel" ONCHANGE="OnChangeUserDelete(this.form)" >  
                 <? $db=new dbio("sgta_index");
                    $db->OpenDb();
                    $strQry="SELECT email FROM users, entities WHERE entities.entity_name='" .$selentityname. " AND users.entity_id=entities.id ORDER BY email;";
                    $db->DoQuery($strQry);
                    while($db->FetchRow()) {
                        $usernamex= $db->FetchField("email");
                        $usernames[]= $usernamex;
                    } 
                    $cnt = count($usernames);
	            for($i=0; $i<$cnt; $i++) {
                        echo "<option value='$usernames[$i]'";
                        if($seluserdel==$usernames[$i])	{
                            echo " selected='selected'";
			}
			echo ">$usernames[$i]</option>";
		    }
		 ?>
             </select>
	   </TD>
         </TR>
         <TR>
	    <TD style='text-indent:410px;'>
		<input type="Submit" value="Delete User">
	    </TD>
	</TR>
	</FORM>
	</TABLE>
	<?
	$tabs->end();
        $tabs->start("Add Entity"); 
        ?>
	<center>
	<h3>ADD ENTITY: <font color="FF0000"> 
            <? if ($currtab==4) {
                   if($_GET['login_err']==0) 
                       echo ""; 
                   else {
	   	       echo $_SESSION['err_login_msg'];
                       $_SESSION['err_login_msg'] = "";
                   }
               }	   		


            ?></font></h3>
	</center>
        <TABLE>
        <TR>
           <TD>
           <FORM style="padding: 4 0;" method="post" action="adm_addentity.php">
           <input type="hidden" name="currtab" value="4">
	    <TR>
               <TD style='text-indent:240px;'>
		   <big>New Entity Name:</big> 
		   <input type="text" size="30" style="text-align:left;" name="newentity" value="<?echo "type new entity name here"?>">
               </TD>
            </TR>
            <TR>
	      <TD style='text-indent:400px;'>
               <input type="Submit" value="Save Entity" >
              </TD>
           </TR>
           </FORM>
        </TR>
        </TABLE>
        <?
	$tabs->end();
        $tabs->start("Modify Entity"); 
        ?>
	<center>
	<h3>MODIFY ENTITY: <font color="FF0000"> 
            <? if ($currtab==5) {
                   if($_GET['login_err']==0) 
                       echo ""; 
                   else {
	   	       echo $_SESSION['err_login_msg'];
                       $_SESSION['err_login_msg'] = "";
                   }
               }	   		
            ?></font></h3>
	</center>
        <TABLE>
        <TR>
           <TD style="text-indent:300px;" >
           <FORM style="padding: 4 0;" method="get" action="adm_modentity.php">
           <input type="hidden" name="currtab" value="5">
	    <big>Select Entity to Modify : </big>
	    <select size="1" style="font-size:11pt;" name="selentityname" ONCHANGE="OnChangeEntity(this.form)">
               <? $cnt = count($entities);
	            for($i=0; $i<$cnt; $i++) {
                        echo "<option value='$entities[$i]'";
                        if($selentityname==$entities[$i])  {
                            echo " selected='selected'";

                        }
                        echo ">$entities[$i]</option>";
                    }
                ?>
            </select>
            <TR>
               <TD style='text-indent:240px;'>
		   <big>Modified Entity Name:</big> 
		   <input type="text" size="30" style="text-align:left;" name="modentityname" value="<?echo "type modified entity name here"?>">
               </TD>
            </TR>
            <TR>
	      <TD style='text-indent:400px;'>
               <input type="Submit" value="Update Entity" >
              </TD>
           </TR>
           </FORM>
           </TD>
        </TR>
        </TABLE>
        <?
        $tabs->end();
        $tabs->start("Delete Entity"); 
        ?>
	<center>
	<h3>DELETE ENTITY: <font color="FF0000"> 
            <? if ($currtab==6) {
                   if($_GET['login_err']==0) 
                       echo ""; 
                   else {
	   	       echo $_SESSION['err_login_msg'];
                       $_SESSION['err_login_msg'] = "";
                   }
               }	   		
            ?></font></h3>
	</center>
        <TABLE>
        <TR>
           <TD style="text-indent:300px;" >
           <FORM style="padding: 4 0;" method="get" action="adm_adduser.php">
	    <big>Select Entity to Delete : </big>
	    <select size="1" style="font-size:11pt;" name="selentityname" ONCHANGE="OnChangeEntity(this.form)">
               <? $cnt = count($entities);
	            for($i=0; $i<$cnt; $i++) {
                        echo "<option value='$entities[$i]'";
                        if($selentityname==$entities[$i])  {
                            echo " selected='selected'";

                        }
                        echo ">$entities[$i]</option>";
                    }
                ?>
            </select>
           </FORM>
           </TD>
        </TR>
        </TABLE>
        <?
        $tabs->end();

        if($currtab==1) $tabs->active="Add User";
	if($currtab==2) $tabs->active="Modify User";
	if($currtab==3) $tabs->active="Delete User";
        if($currtab==4) $tabs->active="Add Entity";
        if($currtab==5) $tabs->active="Modify Entity";
	if($currtab==6) $tabs->active="Delete Entity";

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
	t = 'gva_adminsu.php?selusermod1=rowform.selusermod1.value&currtab=2';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}

function OnChangeUserDelete(rowform) {
	t = 'gva_adminsu.php?seluserdel=rowform.selusermod.value&currtab=3';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}

function OnChangePLevel(rowform) {
	t = 'gva_adminsu.php?selplevel=rowform.selplevel.value';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}
function OnChangeEntity(rowform) {
	t = 'gva_adminsu.php?selentityname=rowform.selentityname.value';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}


</HTML
