<?php
$mongoDb = new \Mongo('mongodb://localhost:27017/geo');
$mongoCollection = $mongoDb->selectCollection('geo', 'uk_postcodes');
