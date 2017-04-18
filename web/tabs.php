<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
class tabs {
	var $name;
	var $tabs;
	var $active;
	var $current;

	function __construct($name){
		$this->name = $name;
	}

	function start($name){
		if (empty($this->active)){ $this->active = $name; }
		$this->current = $name;
		ob_start();
	}

	function end(){
		$this->tabs[$this->current] = ob_get_contents();
		ob_end_clean();
	}

	function run(){
		if (count($this->tabs) > 0){
			echo "<DIV CLASS='apptabs'>\n";
			$jsClear = "";
			foreach($this->tabs as $tabname => $tabcontent){
				$tabid = "tab_".$this->name."_$tabname";
				$contentid = "tabcontent_".$this->name."_$tabname";
				$jsClear .= "\tdocument.getElementById('$tabid').className = 'apptab_inactive';\n";
				$jsClear .= "\tdocument.getElementById('$contentid').style.display = 'none';\n";
			}
			echo "<script type=\"text/javascript\">\n";
			echo "function tab_".$this->name."(id){\n";
			echo "$jsClear";
			echo "\tdocument.getElementById('tab_".$this->name."_'+id).className = 'apptab_active';\n";
			echo "\tdocument.getElementById('tabcontent_".$this->name."_'+id).style.display = '';\n";
			echo "}\n";
			echo "</script>\n";
			foreach($this->tabs as $tabname => $tabcontent){
				$tabid = "tab_".$this->name."_$tabname";
				$contentid = "tabcontent_".$this->name."_$tabname";
				echo "<DIV CLASS='";
				if ($this->active == $tabname){ echo "apptab_active"; }else{ echo "apptab_inactive"; }
				echo "' ID='$tabid' ";
				echo "onClick=\"tab_".$this->name."('$tabname');\">$tabname</DIV>\n";
			}
			echo "<DIV STYLE='float: left; clear:both;'></DIV>\n";
			foreach($this->tabs as $tabname => $tabcontent){
				$contentid = "tabcontent_".$this->name."_$tabname";
				echo "<DIV ID = '$contentid' CLASS='apptab_content' STYLE='display: ";
				if ($this->active == $tabname){ echo "block"; }else{ echo "none"; }
				echo ";'>$tabcontent</DIV>\n";
			}
			echo "</DIV>\n";
			echo "<DIV STYLE='clear: both;'></DIV>\n";
		}
	}
}

?>
