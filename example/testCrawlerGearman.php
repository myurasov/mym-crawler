<?php


require __DIR__ . '/../../src/modules/AppBase/Application.php';

//

$r = new \mym\Crawler\Repository\MongoRepository();
$r->setMaxDepth(5);
$r->clear();

$urls = [
  new \mym\Crawler\Url('http://fleapop.com/')
];

$r->insert($urls[0]);

$d = new \mym\Crawler\GearmanDispatcher();
$d->setMaxTasks(32);
$d->setRepository($r);
$d->setFunctionName('TestCrawlerWorker');

$d->run();
