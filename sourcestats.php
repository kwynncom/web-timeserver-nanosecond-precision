<?php

require_once('/opt/kwynn/kwutils.php');
require_once('chronyParsed.php');

class parse_sourcestats {
    
    public $np = [];
    
    public static function get() {
	$o = new self();
	return $o->np;
    }
    
    private function __construct() {
	$this->do10();
    }
    
    private function do10() {
	$ret = [];
	$ret['np_timing'] = [];
	
	chrony_parse::popTime($ret['np_timing'], 'np_before_ss_call');
	$r = shell_exec('/usr/bin/chronyc sourcestats'); 
	chrony_parse::popTime($ret['np_timing'], 'np_after_ss_call');
	
	kwas($r && is_string($r), 'invalid sourcestats result - string');
	kwas(preg_match('/\b\S+\s+(\d+)\s+\d+\s+(\d+)([dhm]?)/', $r, $ms) && isset($ms[2]), 'sourcestats invalid result - regex'); // see ssExOut in README
	$ss = $ms[2] * (isset($ms[3]) ? 
		  self::dhmsX($ms[3]) : 1 );
	$np = intval($ms[1]);
	$ret = array_merge($ret, [ 'np'   => $np, 'np_span' => $ss ]);
	$this->np = $ret;
    }
    
    public static function dhmsX($uin) {
	kwas($uin && is_string($uin), 'invalid argument dhmsMultiplier()');
	$u = trim($uin[0]);
	switch($u) {
	    case 'd' : return 84600; break;
	    case 'h' : return  3600; break;
	    case 'm' : return    60; break;
	    case 's' : return     1; break;
	    default  : return     1; break;
	}
    }
}

if (didCLICallMe(__FILE__)) new parse_sourcestats();