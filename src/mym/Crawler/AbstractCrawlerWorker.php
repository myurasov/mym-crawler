<?php

namespace mym\Crawler;

use mym\Crawler\Processor\ProcessorPool;
use mym\GearmanTools\GearmanWorkerInterface;
use mym\GearmanTools\Utils as GearmanToolsUtils;
use Psr\Log\LoggerInterface;

abstract class AbstractCrawlerWorker implements GearmanWorkerInterface
{
  /**
   * @var ProcessorPool
   */
  protected $processorPool;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  public function run(\GearmanJob $job)
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

    $this->logger->info($message);

    $result = GearmanToolsUtils::packMessage($result);

    return $result;
  }

  /**
   * Sets a logger instance on the object
   *
   * @param LoggerInterface $logger
   * @return null
   */
  public function setLogger(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }

  public function getLogger()
  {
    return $this->logger;
  }

  public function setProcessorPool($processorPool)
  {
    $this->processorPool = $processorPool;
  }

  public function getProcessorPool()
  {
    return $this->processorPool;
  }
}