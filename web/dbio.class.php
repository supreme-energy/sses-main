<?php
//	Written by: Richard Gonsuron
//	Modified by: John Arnold
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

class dbio {
	var $hostname;
	var $username;
	var $password;
	var $dbname;
	var $dbh, $stmt, $result, $query;
	var $error_print;

	function dbio($dbn="",$ep=false) {
		$this->hostname='localhost';
		$this->username='umsdata';
		$this->password='umsdata';
		$this->error_print=$ep;
		if($dbn=='') $this->dbname='sgta_template';
		else $this->dbname=$dbn;
	}

	function OpenDb() {
		try {
			$this->dbh = new PDO("pgsql:host=$this->hostname; dbname=$this->dbname",$this->username,$this->password);
		}
		catch(PDOException $e) {
			echo $e->getMessage();
			return 0;
		}
		return 1;
	}

	function CloseDb() {
		$this->dbh = null;
	}

	function DoQuery($qbuf) {
		$q=$qbuf;
		// special case for MySQL equivilent
		if(stripos($q, "SHOW TABLES")!==false) {
			$q = "select tablename from pg_tables where tablename !~'^pg_+' " .
				"AND tablename !~'^sys_+' ORDER BY tablename;";
		}
		try {
			$this->query=$q;
			// $this->stmt=$this->dbh->query($q);
			$this->stmt=$this->dbh->prepare($q);
			$this->stmt->execute();
			$err=$this->stmt->errorCode();
			if($err != 0 && $this->error_print) {
				print "Error $err in query: ";
				echo $this->query;
				print "<br>";
				// echo $this->stmt->errorInfo();
				// print "<br>";
			} else if($err !=0) {
				try{
					error_log("Error $err in query: ".$this->query."\n",3,"error.log");
				} catch(Exception $e) {
					echo 'error writting to error log';
				}
			}
			return $this->stmt->errorCode();
		}
		catch(PDOException $e) {
			echo $e->getMessage();
			return 1;
		}
	}

	function FetchRow($rownum=NULL) {
		$this->result=$this->stmt->fetch(PDO::FETCH_ASSOC);
		return $this->result;
	}

	function FetchObj($rownum=NULL) {
		$this->result=$this->stmt->fetch(PDO::FETCH_OBJ);
		return $this->result;
	}

	function FetchField($fieldname) {
		$retval="";
		if(isset($this->result) and is_array($this->result))
		{
			foreach($this->result as $key=>$val)
			{
				if($key==$fieldname)
				{
					$retval=$val;
					break;
				}
	    	}
	    }
		return $retval;
	}

	function FetchNumRows() {
		return $this->stmt->rowCount();
	}

	function FetchFieldNum($num, $fieldname) {
		$this->DoQuery($this->query);
		$retval="$fieldname not found";
		$cnt=$this->FetchNumRows();
		for($i=0; $i<=$num && $i<$cnt; $i++) {
			$this->FetchRow();
			if($i==$num)
				$retval=$this->FetchField($fieldname);
		}
		return $retval;
	}

	function ClearDb() {
		// $this->DoQuery("DROP SCHEMA public CASCADE");
		// $this->DoQuery("drop database umsdata");
		// $this->DoQuery("create database umsdata");
	}

	function FreeResult() {
	}

	function ColumnExists($tn, $col) {
		$this->query=sprintf("select column_name from information_schema.columns where table_name='%s';", $tn);
		$this->DoQuery($this->query);
		$num=$this->FetchNumRows();
		for($i=0; $i<$num; $i++) {
			$this->FetchRow();
			$name=$this->FetchField("column_name");
			if($name==$col)
				return 1;
		}
		return 0;
	}
	function TableExists($tn) {
		$this->query=sprintf("select * from pg_tables where schemaname='public' and tablename='%s';", $tn);
		$this->DoQuery($this->query);
		return $this->FetchNumRows();
	}
}
?>
