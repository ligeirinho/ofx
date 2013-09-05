<?php

require_once 'vendor/autoload.php';

$ofx = new \Sinergia\Ofx\Ofx('tests/fixtures/itau-no-xml.ofx');

var_export($ofx->getTransactions());

