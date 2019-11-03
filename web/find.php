<?php
ini_set('display_errors', 1);

require dirname(__FILE__) . '/connection.php';

$found = $mongoCollection->find(array('pos' => array('$near' => array(-3.127941, 51.525562), '$maxDistance' => 4000)))->limit(30);
while ($found->hasNext()) {
    $rec = $found->getNext();
    var_dump($rec);
}
