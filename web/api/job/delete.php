<?
require_once("../../dbio.class.php");

$todelete = isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '';
$never_delete = array("sgta_template", "sgta_index", "template0", "template1", "postgres");
$db=new dbio("postgres");
$db->OpenDb();
$db->DoQuery('SELECT datname FROM pg_database WHERE datistemplate = false;');
$db2 = new dbio("sgta_index");
$db3 = new dbio("sgta_index");
//$db2->DoQuery("delete from dbindex where name='$todelete'");
echo "delete from dbindex where name='$todelete'";
$db2->OpenDb();
$db3->OpenDb();
while($db->FetchRow()){
    $name = $db->FetchField("datname");
    if(in_array($name, $never_delete) === true){
        continue;
    }
    echo "$name\n";
    if(strpos($name, 'sgta_') !== false ){
        $db3->DoQuery("select * from dbindex where name='$name'");
        $res = $db3->FetchRow();
        print_r($res);
        if($res){
            //$db2->DoQuery("Drop database if exists $name");
            echo "Drop database if exists $name\n";
        }
    }
}
?>