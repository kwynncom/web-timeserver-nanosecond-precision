<?php

require_once('/opt/kwynn/kwutils.php');

class chrony_log_parse {
    
    const tailn = 6;
	const path  = '/var/log/chrony/';
	const files = ['tracking.log', 'statistics.log', 'measurements.log'];
    
    public $an10 = [];
    
    private function __construct() {
		$this->load10();
		$this->do10();
		$this->do20();
		$this->an10['maxe'] = $this->maxe;
    }
    
    public static function get() {
		$o = new self();
		return $o->linea;
    }
    
    private function do20() {
	$a = $this->lpa10;
	$fi = 0;
	$n = count($a);
	if ($n < 2) return;
	$li = $n - 1;
	$spans = $a[$fi]['ts'] - $a[$li]['ts']; 
	
	$change = 0;
	for($i=0; $i < $li; $i++) $change += abs($a[$i  ]['freq_corr'] - 
					         $a[$i+1]['freq_corr']);

	unset($fi, $li, $i, $a);
	
	$change_freq_corr = $change; unset($change);
	
	$this->an10 = get_defined_vars();
	
	return;
	
	
    }
    
    public static function getE($e) {
	kwas(preg_match('/^\d+\.\d+e[+-]\d+$/', $e, $ms), "getE failed with input $e");
	return floatval($ms[0]);
	
    }
    
    private function do10() {
	$ret = [];
	$maxe = false;
	foreach($this->linea as $ts => $snas)
	{
		if (!isset($snas['t'])) continue;
		$l =	   $snas['t'];
	    $t = [];
	    $ds = $t['ds'] = $l[0] . ' ' . $l[1] . ' UTC';
	    $ts = $t['ts'] = strtotime($ds);
	    $t['dss'] = date('h:i:s A', $ts);
	    if (!preg_match('/^-?\d+\.\d+$/',$l[4], $ms)) continue;
	    $t['freq_corr'] = floatval($ms[0]);
	   
	    if ($maxe === false) $maxe = self::getE($l[13]);
    
	    $ret[] = $t;
	    continue;
	}
	
	$this->maxe = $maxe;
	$this->lpa10 = $ret;
    }
    
    private function load10() {
		
		foreach(self::files as $f) {
		
			$fsn = substr($f, 0, 1);
			
			$cmd  = '';
			$cmd .= 'tail -n ';
			$cmd .= self::tailn + 3; // account for headers
			$cmd .=  ' ';
			$cmd .= self::path . $f;
			$cmd .= ' | tac';

			$t = shell_exec($cmd); kwas($t && is_string($t) && strlen($t) > 30, 'chrony tracking file load fail shell');
			$ret = [];
			$la = explode("\n", $t); 
			$lii = 0;
			foreach($la as $l) {
				if (!$l) continue; // the blank string following the last line
				if (strpos($l, '='   ) !== false) continue; // header =====
				if (strpos($l, 'Date') !== false) continue; // header labels
				$a = preg_split('/\s+/', $l);
				$this->linea[$a[0] . ' ' . $a[1]][$fsn] = $a;
				if (++$lii >= self::tailn) break;
			}
		}
		return;
    }
}

if (didCLICallMe(__FILE__)) print_r(chrony_log_parse::get());
