<?php

require __DIR__ . '/../../src/modules/AppBase/Application.php';

$p = new \mym\Component\GearmanTools\GearmanTaskPool();

$p->setFunctionName('pool_test');
$p->setMaxTasks(10);

$p->setWorkloadCallback(function(){
  return (string) time();
});

$p->setTaskCallback(function($data) {
  var_dump($data); // xxx
});

$p->run();
