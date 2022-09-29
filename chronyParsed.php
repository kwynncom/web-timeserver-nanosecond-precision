<?php

require_once('/opt/kwynn/kwutils.php');

class chrony_parse {

    const minSize = 150;
    
private static function cmd() { 
    
    $cmd = 'chronyc tracking';
    $raw = shell_exec($cmd); $now = time(); kwas($raw && is_string($raw) && strlen($raw) >= self::minSize, '$ ' . $cmd . ' failed 10 ');
    $res = trim($raw); 
    $anl = explode("\n", $res); unset($res); kwas($anl && is_array($anl), '$ ' . $cmd . ' failed 20');
    $a = [];
    foreach($anl as $row) {
	$ac = explode(' : ', $row);
	if (!$ac || count($ac) !== 2) continue;
	if (   trim($ac[0]) &&  trim($ac[1]))
	    $a[trim($ac[0])] =  trim($ac[1]);
    }
    
    return ['cmd' => $cmd, 'basic_array' => $a, 'raw_cmd_result' => $raw];
}

private static function pop30($ain, &$arefin) {
   
    $fs = ['Root dispersion', 'RMS offset', 'Root delay'];
    foreach($fs as $f) {
	kwas(preg_match('/^(\d+\.\d+) seconds/', $ain[$f], $ms), "field $f regex fail");
	$arefin[$f] = floatval($ms[1]);
	continue;
    }
    unset($ms);
    $f = 'Residual freq';
    
    kwas(preg_match('/^([-+]?\d+\.\d+) ppm/', $ain[$f], $ms), "$f regex fail");
    $arefin[$f] = floatval($ms[1]);
}

private static function pop40($s, &$aref, $k) {
	preg_match('/^([-+]?\d+\.\d+) seconds/', $s, $m);
	$aref[$k] = floatval($m[1]);
}

public static function get() {
    $r = [];
	
	try {
		$tsk = 'first_server_timestamp';
		self::popTime($r, $tsk);
		$r = array_merge($r, self::cmd());
		$r['detailed_array'] = self::get20($r['basic_array']);
		self::pop30($r['basic_array'], $r['detailed_array']);
		$lok = 'Last offset';
		self::pop40($r['basic_array'][$lok], $r['detailed_array'], $lok); unset($lok);
		$tsk = 'last_server_timestamp';

		self::popTime($r, $tsk);
	} catch(Exception $ex) { return $r; }
    
    if (!self::didCallMe()) return $r;

	if (!iscli()) {
		header('Content-Type: application/json');
		echo(json_encode($r));
	}
    else var_dump($r);
    
}

public static function popTime(&$ta, $k) {
    $r = [];
    if (function_exists('nanotime') && 1) {
	$ts = nanotime();
	$r['number'] = $ts;
	$r['unit_long']  = 'nanoseconds in UNIX Epoch';
	$r['unit'] = 'ns';
	$r['number_type'] = 'integer';
    } else {
	$s = microtime();
	$ts = time();
	$r['string'] = $s;
	$a = explode(' ', $s); kwas(isset($a[1]), 'microtime fail');
	$f = $a[0] + $a[1];
	kwas(abs($f - time()) < 4, 'microtime fail 20');
	$r['number'] = $f;
	$r['precision'] = 0.0001;
	$r['unit'] = 'us';
	$r['unit_long'] = 'microseconds in UNIX Epoch';
	$r['note'] = 'precision is limited due to floating point capacity';
	$r['number_type'] = 'float';
    }
    
    $ta[$k] = $r;
}

public static function get20($a) {
    
    try {


    $ret = [];

    $k = 'Reference ID';
        
    $r = $a[$k];
    preg_match('/([0-9A-Z]+) \(([^\)]+)\)/', $r, $matches);
    
    kwas(isset($matches[1]) && $matches[1] && is_string($matches[1]) && strlen(trim($matches[2])) >= 8, 'rID regex fail');    
    kwas(isset($matches[2]) && $matches[2] && is_string($matches[2]) && strlen(trim($matches[2])) >= 2, 'rID regex fail');
    
    $ret[$k]['raw']	       = trim($matches[0]);
    $ret[$k]['iphex']	       = trim($matches[1]);
    $ret[$k]['iphuman'] = $iph = trim($matches[2]);
    
    if (isAWS() && $iph === '169.254.169.123') $ret['isaws'] = 'using Amazon Web Services Time Sync Service timeserver';
    else $ret['isaws'] = false;
    
    $key = 'Ref time (UTC)';
    kwas(isset($a[$key]), 'no UTC ref time');
    
    $ts = strtotime($a[$key] . ' UTC');
    $ret[$key]['raw'] = $a[$key];
    $ret[$key]['UNIX Epoch'] = $ts;
    $sago = time() - $ts; unset($ts);
    $ret[$key]['s_ago']  = $sago;
    $ret[$key]['hours_ago']  = $sago / 3600;  unset($key);
    
    
    $k = 'System time';
    $ret[$k]['raw'] = $st = $a[$k];
    
    preg_match('/(^\d+\.\d+) seconds (\w+) of NTP time/', $st, $matches); unset($st); kwas(isset($matches[2]), 'regex fail offset'); 


    if      ($matches[2] === 'fast') { $sign = '+'; $mult =  1; }
    else if ($matches[2] === 'slow') { $sign = '-'; $mult = -1; }
    else kwas(false, 'neither fast or slow');
    
    $f = floatval($matches[1]);
    $ret[$k]['float'] = $f * $mult;
    $ret[$k]['direction'] = $matches[2];
    $ret[$k]['sign']      = $sign;
    $ret[$k]['ns']        = intval($f * 1000000000); unset($f, $matches, $sign, $mult, $k);

    $k = 'Update interval';
    $ret[$k]['raw'] = $ui = $a[$k];
   
    preg_match('/(\d+\.?\d*)\s+seconds/', $ui, $m); kwas(isset($m[1]), 'update interval parse fail');

    $ret[$k]['raw'] = $ui;
    $ret[$k]['s'  ] = $s = floatval($m[1]);
    $ret[$k]['hours'] = $s / 3600;

    return $ret;
     
    }
    catch (Exception $ex) { 
	if (PHP_SAPI === 'cli') throw $ex;
	return false; 
    }
} // func

public static function didCallMe() {
	if (didCLICallMe(__FILE__)) return TRUE;
	if (!isset($_SERVER['REQUEST_URI'])) return false;
	$h =	   $_SERVER['REQUEST_URI'];
	$n = basename(__FILE__);
	if (strpos($h, $n) !== false) return TRUE;
	return false;
}

} //class

if (chrony_parse::didCallMe()) chrony_parse::get(); 
