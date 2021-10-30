<?php

require_once('chronyParsed.php');
require_once('sourcestats.php');
require_once('logs.php');

class chrony_anal {
    
    const tfile = '/var/log/chrony/tracking.log';
    
    public function __construct() {
	$this->np = parse_sourcestats::get(); 
	$this->tr = chrony_parse::get(true);
//	$this->an10 = chrony_log_parse::get(self::tfile);
	$this->do10();
	return;
    }
    
    private function do10() {
	kwas(isset($this->tr['detailed_array']['Ref time (UTC)']['s_ago'    ]), 'ref time s_ago ne');
	kwas(isset($this->tr['detailed_array']['Ref time (UTC)']['UNIX Epoch']), 'ref time UE ne');
	kwas(isset($this->tr['detailed_array']['Ref time (UTC)']['hours_ago']), 'ref time hrs ne');
	$phr =	   $this->tr['detailed_array']['Ref time (UTC)']['hours_ago'];
	kwas(isset($this->tr['detailed_array']['RMS offset']), 'RMS O ne');	
	$rms =	   $this->tr['detailed_array']['RMS offset'];
	kwas(isset($this->tr['detailed_array']['Residual freq']), 'RMS O ne');
	$rfr =     $this->tr['detailed_array']['Residual freq'];
	kwas(isset($this->tr['detailed_array']['Root dispersion']), 'RMS O ne');
	$rdi =     $this->tr['detailed_array']['Root dispersion'];
	
	return;
    }
    
}

if (didCLICallMe(__FILE__)) new chrony_anal();