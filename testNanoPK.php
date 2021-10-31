<?php

if (PHP_SAPI !== 'cli') header('Content-Type: text/plain');

$name = 'nanopk';
echo($name . ' version: ' . phpversion($name) . "\n");

$fs = ['time', 'nanotime'];
foreach($fs as $f) var_dump($f());
var_dump(nanopk(NANOPK_VERSION));

echo('this file version 2021/10/31 7:30pm' . "\n");

// originally from https://github.com/kwynncom/nano-php-extension/blob/main/test.php
