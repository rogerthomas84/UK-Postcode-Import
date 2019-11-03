<?php
ini_set('display_errors', 1);

require dirname(__FILE__) . '/connection.php';
require dirname(__FILE__) . '/../lib/NorthingsEastingsToCoordinates.php';
require dirname(__FILE__) . '/../lib/ProcessCsvs.php';

$instance = new ProcessCsvs(realpath(dirname(__FILE__)) . '/csvs', $mongoCollection);
$instance->run();
