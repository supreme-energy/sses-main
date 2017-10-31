<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?php
class dbio {
	var $dbname;
	var $dbh, $query;
	var $stmt, $datarow;
	var $datacount;
	var $dataarray;

    function dbio() {
		$this->dbname='./test.db';
	}

	function OpenDb() {
		try {
			$this->dbh = new PDO("sqlite:$this->dbname");
		}
		catch(SQLiteException $e) {
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
		if(stripos($q, "SHOW TABLES")!==false)
			$q = "SELECT name FROM sqlite_master WHERE type = 'table';";
		try {
			$this->query=$q;
			if(stristr($q, "SELECT")!=FALSE) {
				$this->stmt=$this->dbh->query($q);
				if($this->stmt!=FALSE) {
					$this->dataarray=$this->stmt->fetchAll();
					$this->datacount=count($this->dataarray);
					$this->stmt=$this->dbh->query($q);
				}
				else return FALSE;
			}
			else {
				$this->stmt=$this->dbh->query($q);
				// $this->datacount=$this->stmt->rowCount();
			}
			// $this->stmt=$this->dbh->prepare($q);
			// $this->stmt->execute();
			// $this->datacount=$this->stmt->rowCount();
			$this->dataptr=0;
			return $this->stmt;
		}
		catch(SQLiteException $e) {
			echo $e->getMessage();
			return 1;
		}
	}

	function FetchRow($rownum=NULL) {
		if($this->stmt==FALSE)	return FALSE;
		$this->datarow=$this->stmt->fetch();
		return $this->datarow;
	}

	function FetchField($fieldname) {
		return $this->datarow[$fieldname];
	}

	function FetchNumRows() {
		return $this->datacount;
		// printf("rowCount: %d\n", $this->stmt->rowCount());
		// return $this->stmt->rowCount();
	}

	function ClearDb() {
	}

	function ColumnExists($tn, $col) {
		return 0;
	}
	function TableExists($tn) {
		return 0;
	}
}
?>
