<?php

require_once 'vendor/autoload.php';

use FDL\FDL;

$f = new FDL(['sample.fdl', 'sample2.fdl']);
$f->run();

