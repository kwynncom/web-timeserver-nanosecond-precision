<?php

require_once('chronyParsed.php');
require_once('sourcestats.php');

class chrony_anal {
    public function __construct() {
	$this->np = parse_sourcestats::get(); 
	$this->tr = chrony_parse::get(true);
	$this->do10();
	return;
    }
    
    private function do10() {
	kwas(isset($this->tr['detailed_array']['Ref time (UTC)']['s_ago'    ]), 'ref time s_ago ne');
	kwas(isset($this->tr['detailed_array']['Ref time (UTC)']['UNIX Epoch']), 'ref time UE ne');
	kwas(isset($this->tr['detailed_array']['Ref time (UTC)']['hours_ago']), 'ref time hrs ne');
	return;
    }
    
}

if (didCLICallMe(__FILE__)) new chrony_anal();