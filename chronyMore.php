<?php

require_once('chronyParsed.php');
require_once('sourcestats.php');

class chrony_anal {
    public function __construct() {
	$this->np = parse_sourcestats::get(); 
	$this->tr = chrony_parse::get(true);
	return;
    }
}

if (didCLICallMe(__FILE__)) new chrony_anal();