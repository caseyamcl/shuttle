<?php

// ------------------------------------------------------------------

$autoloadFile = __DIR__.'/../vendor/autoload.php';

if ( ! is_readable($autoloadFile)) {
    throw new RuntimeException('Install dependencies to run test suite (composer install --dev).');
}

require_once($autoloadFile);
