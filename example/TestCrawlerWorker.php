<?php

require __DIR__ . '/../../../src/modules/AppBase/Application.php';

//

use mym\Crawler\Url;
use mym\Crawler\Processor\ProcessorPool;
use mym\Component\GearmanTools\Utils as GearmanToolsUtils;

use AppBase\Crawler\TestProcessor;

class TestCrawlerWorker implements GearmanManagerWorkerInterface
{
  protected $processorPool;

  public function __construct()
  {
    $this->processorPool = new ProcessorPool();
    $this->processorPool->addProcessor(new TestProcessor());
  }

  public function run(\GearmanJob $job, &$log)
  {
    $url /* @var $url Url */ = GearmanToolsUtils::unpackMessage($job->workload());

    $this->processorPool->process($url);

    $result = [
      'url' => $url,
      'extractedUrls' => $this->processorPool->getExtractedUrls()
    ];

    $extractedurlsCount = count($this->processorPool->getExtractedUrls());

    $log[] = "url (D={$url->getDepth()}) (S={$url->getStatus()}) (+=$extractedurlsCount): {$url->getUrl()}";

    $result = GearmanToolsUtils::packMessage($result);

    return $result;
  }
}