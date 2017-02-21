<?php
class CompanyTrading {
	function __construct($timestamp,$symbol,$quantity,$price){
		$this->symbol=$symbol;
		$this->timestamp=$timestamp;
		$this->time_between=0;
		$this->quantity=$quantity;
		$this->price_x_quantity=$price*$quantity;
		$this->max_price=$price;
	}	
	function add($timestamp,$symbol,$quantity,$price){
		$timedif = $timestamp-$this->timestamp;
		$this->timestamp=$timestamp;
		$this->quantity+=$quantity;
		$this->price_x_quantity+=$price*$quantity;
		if($price>$this->max_price){
			$this->max_price=$price;
		}
		if($timedif>$this->time_between){
			$this->time_between=$timedif;
		}
	}
	function weightedAverage(){
		return $this->price_x_quantity/$this->quantity;
	}
	function getCSVString(){
		$resthis= $this->symbol.",".$this->time_between.",".$this->quantity.",".floor($this->weightedAverage()).",".$this->max_price."\n";
		return $resthis;
	}
};


class MyFileReader{

	function open_and_read($filename){
		$this->resultsarray=array();
		$file = fopen("input.csv","r");
		
		while($resarray = fgetcsv($file)){
		$symbol = $resarray[1];
		$timestamp = $resarray[0];
		$quantity = $resarray[2];
		$price = $resarray[3];
		//check to if symbol was previously read
		if(isset($this->resultsarray[$symbol])){
			$object = $this->resultsarray[$symbol];
			$object->add($timestamp,$symbol,$quantity,$price);
			$this->resultsarray[$symbol]=$object;
		} else {
			//initialize the symbo on first read
			$object = CompanyTrading($timestamp,$symbol,$quantity,$price);
			$resultsarray[$symbol]=$object;
		}
	}

	function write_result($filename){
		$outstr="";
		ksort($this->resultsarray);
		foreach($this->resultsarray as $r){
			$outstr.=$r->getCSVString();
		}
		file_put_contents($filename,$outstr);		
	}
}

$filereader = new MyFileReader();
$filereader->open_and_read("sample.csv");
$filereader->write_result("testoutput.csv");
	

?>