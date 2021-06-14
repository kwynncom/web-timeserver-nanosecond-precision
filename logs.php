<?php

require_once('/opt/kwynn/kwutils.php');

class chrony_log_parse {
    
    const tailn = 6;
    
    public function __construct($file) {
	$this->load10($file);
	$this->do10();
	// $this->do20();
    }
    
    private function do10() {
	$a = $this->linea;
	foreach($a as $l) {
	    $t = [];
	    $ds = $t['ds'] = $l[0] . ' ' . $l[1] . ' UTC';
	    $ts = $t['ts'] = strtotime($ds);
	    $t['dss'] = date('h:i:s A', $ts);
	    if (!preg_match('/^-?\d+\.\d+$/',$l[4], $ms)) continue;
	    $t['freq_corr'] = floatval($ms[0]);
	    continue;
	}
    }
    
    private function load10($fpath) {
	$cmd  = '';
	$cmd .= 'tail -n ';
	$cmd .= self::tailn + 3; // account for headers
	$cmd .=  ' ';
	$cmd .= $fpath;
	$cmd .= ' | tac';
	
	$t = shell_exec($cmd); kwas($t && is_string($t) && strlen($t) > 30, 'chrony tracking file load fail shell');
	$ret = [];
	$la = explode("\n", $t); 
	$i = 0;
	foreach($la as $l) {
	    if (!$l) continue; // the blank string following the last line
	    if (strpos($l, '='   ) !== false) continue; // header =====
	    if (strpos($l, 'Date') !== false) continue; // header labels
	    $a = preg_split('/\s+/', $l);
	    $ret[] = $a;
	    if (++$i >= self::tailn) break;
	}
	
	$this->linea = $ret;
	return;
    }
	
	
}

if (didCLICallMe(__FILE__)) new chrony_log_parse('/var/log/chrony/tracking.log');