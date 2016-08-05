<?php
include_once("sses_include.php");
//For security reasons, don't display any errors or warnings. Comment out in DEV.
error_reporting(1);

require_once("dbio.class.php");

//start session
class LogMeIn {
    //table fields
    var $roles_db = 'sgta_index';          //Users table name
    var $user_table = 'users';          //Users table name
    var $user_column = 'email';         //USERNAME column (value MUST be valid email)
    var $pass_column = 'password';      //PASSWORD column
    var $user_level = 'plevel';      //(optional) userlevel column
 
    //encryption
    var $encrypt = true;       //set to true to use md5 encryption for the password
    
    //login function
    function login($username, $password){
        $db=new dbio("sgta_index");
        //conect to DB
        $db->OpenDb();
 
        //check if encryption is used
        if($this->encrypt == true){
            $password = md5($password);
        }

        //execute login via qry function that prevents MySQL injections
        $db->DoQuery("SELECT * FROM users WHERE email ='$username' AND password = '$password';");

        if($db->FetchRow()) {
            if ($db->FetchField("email") != "" && $db->FetchField("password") != "" ) {
                //register sessions
                //you can add additional sessions here if needed
                $_SESSION['loggedin'] = $db->FetchField("id");
                //userlevel session is optional. Use it if you have different user levels
                $_SESSION['userlevel'] = $db->FetchField("plevel");
                $_SESSION['entity_id'] = $db->FetchField("entity_id");
                return true;
            }else{
                session_destroy();
                return false;
            }
        }else{
            return false;
        }
    }

    function login_admin($table, $username, $password){
        $db=new dbio($this->roles_db);
        //conect to DB
        $db->OpenDb();
        //make sure table name is set
        if($this->user_table == ""){
            $this->user_table = $table;
        }

        //check if encryption is used
        if($this->encrypt == true){
            $password = md5($password);
        }

        //execute login via qry function that prevents MySQL injections
        $db->DoQuery("SELECT * FROM ".$this->user_table." WHERE ".$this->user_column."='$username' AND ".$this->pass_column." = '$password' AND userlevel = 1;");

        if($db->FetchRow()) {
            if ($db->FetchField($this->user_column) != "" && $db->FetchField($this->pass_column) != "" ) {
                //register sessions
                //you can add additional sessions here if needed
                $_SESSION['loggedin'] = $row[$this->pass_column];
                //userlevel session is optional. Use it if you have different user levels
                $_SESSION['userlevel'] = $row[$this->userlevel];
                return true;
            }else{
//                session_destroy();
                return false;
            }
        }else{
            return false;
        }
    }

    function logincheck($logincode, $user_table, $pass_column, $user_column){
        $db=new dbio($this->roles_db);
        //conect to DB
        $db->OpenDb();
        //make sure password column and table are set
        if($this->pass_column == ""){
            $this->pass_column = $pass_column;
        }
        if($this->user_column == ""){
            $this->user_column = $user_column;
        }
        if($this->user_table == ""){
            $this->user_table = $user_table;
        }
        //exectue query
        $result = $db->DoQuery("SELECT * FROM ".$this->user_table." WHERE ".$this->pass_column." = 'logincode';" , $logincode);
        if($db->FetchRow()) {
                return true;
            }else{
                return false;
            }
        }

    //logout function
    function logout(){
//        session_destroy();
    }
 
    //login form
    function loginform($formname, $formclass, $formaction){
        $_SESSION['err_login_msg'] = " ";
        
        //conect to DB
    	$db=new dbio($this->roles_db);
        //conect to DB
        $db->OpenDb();
        echo'
<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'">
<div  style="text-indent:15px"><label for="username" >Login with Email: </label>
<input name="username" id="username" type="text"></div>
<br>
<div style="text-indent:50px"><label for="password">Password: </label>
<input name="password" id="password" type="password" autocomplete="off"></div>
<input name="action" id="action" value="login" type="hidden">
<br>
<div>
<input name="submit" id="submit" value="Login" type="submit"></div>
<br>
</form>
 
';
    }
}
?>
