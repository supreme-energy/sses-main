<?php
/*
 * Created on Mar 23, 2016
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 function OpenConnection()
{
    try
    {
        $serverName = "tcp:nnvloxu5qz.database.windows.net,1433";
        $connectionOptions = array("Database"=>"RT",
            "Uid"=>"sses@nnvloxu5qz", "PWD"=>"ss`&123Admin");
        $conn = sqlsrv_connect($serverName, $connectionOptions);
        if($conn == false)
            die(print_r(sqlsrv_errors()));
         return $conn;
    }
    catch(Exception $e)
    {
        echo("Error!");
    }
   
}
function ReadData()
{
    try
    {
        $conn = OpenConnection();
        $tsql = "SELECT * FROM tblSSES";
        $getProducts = sqlsrv_query($conn, $tsql);
        if ($getProducts == FALSE)
            die(print_r(sqlsrv_errors()));
        $productCount = 0;
        while($row = sqlsrv_fetch_array($getProducts, SQLSRV_FETCH_ASSOC))
        {
           print_r($row);
           echo("<br/>");
            $productCount++;
        }
        sqlsrv_free_stmt($getProducts);
        sqlsrv_close($conn);
    }
    catch(Exception $e)
    {
        echo("Error!");
    }
}
function InsertData()
{
    try
    {
        $conn = OpenConnection();

        $tsql = "INSERT INTO tblsses (pdtID, CSVdata) VALUES ('81D9AE36-196A-4CF6-9D51-64DBC7AA9D9F', 'Insert NEW row...')";
        //Insert query
        $insertReview = sqlsrv_query($conn, $tsql);
        if($insertReview == FALSE)
            die(print_r( sqlsrv_errors()));
        
        sqlsrv_free_stmt($insertReview);
        sqlsrv_close($conn);
    }
    catch(Exception $e)
    {
        echo("Error!");
    }
}
function DeleteRow()
{
    try
    {
        $conn = OpenConnection();

        $tsql = "select * from tblsses";
        //Insert query
        $deleteReview = sqlsrv_query($conn, $tsql);
        if($deleteReview == FALSE)
            die(print_r( sqlsrv_errors()));
       
        $row = sqlsrv_fetch_array($deleteReview, SQLSRV_FETCH_ASSOC);
        
        sqlsrv_free_stmt($deleteReview);
        $tsql = "delete from tblsses where RecID=".$row['RecID'];
        $deleteExecute = sqlsrv_query($conn, $tsql);
        if($deleteExecute == FALSE)
            die(print_r( sqlsrv_errors()));
        sqlsrv_free_stmt($deleteExecute);
        sqlsrv_close($conn);
    }
    catch(Exception $e)
    {
        echo("Error!");
    }
}
//InsertData();
ReadData();
DeleteRow();
?>
