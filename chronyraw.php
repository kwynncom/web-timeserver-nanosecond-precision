<?php

header('Content-Type: text/plain');

$opt = false;
if (isset( $_REQUEST['opt'])) 
	$opt = $_REQUEST['opt'];

if (!$opt || $opt === 'both') {
	echo(shell_exec('chronyc tracking'));
	if (!$opt) exit(0);
}

if ($opt === 'both') echo("\n" . '****** sourcestats ******' . "\n");

echo(shell_exec('chronyc sourcestats'));
