<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../ElasticImport.php';
// First Parameter is the city that you want to crawl 
// Second is limit
set_time_limit(0);
$importer = new Jdecano\ElasticImport();
$importer->purge();
if (isset($_GET['host'])) {
	$importer->setHost(strip_tags($_GET['host']));
}
$importer->generateMappings(file_get_contents(__DIR__.'/mappings.json'));
$importer->generateSessions(100);
echo "Done";