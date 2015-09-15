<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../ElasticImport.php';
// First Parameter is the city that you want to crawl 
// Second is limit
set_time_limit(0);
$importer = new Jdecano\ElasticImport();
$importer->generateSessions(500);
echo "Done";