<?php

error_reporting(E_ALL & ~E_STRICT);

include_once(__DIR__.'/../vendor/autoload.php');
include_once(__DIR__.'/../vendor/papaya/test-framework/src/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();