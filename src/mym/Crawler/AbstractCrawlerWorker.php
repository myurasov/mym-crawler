<?php

namespace mym\Crawler;

use mym\Crawler\Processor\ProcessorPool;
use mym\Component\GearmanTools\Utils as GearmanToolsUtils;

class AbstractCrawlerWorker implements \GearmanManagerWorkerInterface
{
  /**
   * @var ProcessorPool
   */
  protected $processorPool;

  public function run(\GearmanJob $job, &$log)
  {
    $url /* @var $url Url */ = GearmanToolsUtils::unpackMessage($job->workload());

    $error = 0;

    try {
      $this->processorPool->process($url);
      $extractedUrlsCount = count($this->processorPool->getExtractedUrls());
      $message = "{$url->getUrl()} / depth: {$url->getDepth()} / status: {$url->getStatus()} / extracted: $extractedUrlsCount";
    } catch (\Exception $e) {
      $message = "Failed to process url [{$url->getId()}] \"{$url->getUrl()}\": {$e->getMessage()}";
      $error = $e->getCode();
      $error = $error === 0 ? -1 : $error;
    }

    $result = [
      'url' => $url,
      'extractedUrls' => $this->processorPool->getExtractedUrls(),
      'error' => $error,
      'message' => $message
    ];

    $log[] = $message;

    $result = GearmanToolsUtils::packMessage($result);

    return $result;
  }
}