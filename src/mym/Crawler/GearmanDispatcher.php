<?php

/**
 * @copyright 2013, Mikhail Yurasov <me@yurasov.me>
 */

namespace mym\Crawler;

use mym\Crawler\Repository\RepositoryInterface;

use mym\GearmanTools\GearmanTaskPool;
use mym\GearmanTools\Utils as GearmanToolsUtils;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class GearmanDispatcher implements DispatcherInterface
{
  /**
   * Number of tasks to run in parallel
   * @var int
   */
  private $maxTasks = 20;

  /**
   * Gearman servers 'host:port,...'
   * @var string
   */
  private $servers = '127.0.0.1:4730';

  /**
   * Gearman worker function name
   * @var string
   */
  private $functionName= '';

  /**
   * @var RepositoryInterface
   */
  private $repository;

  /**
   * @var LoggerInterface
   */
  private $logger;

  //

  /**
   * @var GearmanTaskPool
   */
  private $gearmanTaskPool;

  //

  public function __construct()
  {
    $this->logger = new NullLogger();
  }

  private function init()
  {
    if (!$this->gearmanTaskPool) {
      $this->gearmanTaskPool = new GearmanTaskPool();

      $this->gearmanTaskPool->setServers($this->servers);
      $this->gearmanTaskPool->setMaxTasks($this->maxTasks);
      $this->gearmanTaskPool->setTaskCallback([$this, '_onTask']);
      $this->gearmanTaskPool->setFunctionName($this->functionName);

      $this->gearmanTaskPool->setWorkloadCallback(function() {
        $url = $this->repository->next();
        return $url ? GearmanToolsUtils::packMessage($url) : false;
      });
    }
  }

  public function run()
  {
    $this->init();
    $this->gearmanTaskPool->run();
  }

  public function _onTask(\GearmanTask $task)
  {
    $data = GearmanToolsUtils::unpackMessage($task->data());

    $url /* @var $url Url */ = $data['url'];
    $extractedUrls = $data['extractedUrls'];
    $error = $data['error'];
    $message = $data['message'];

    // add extracted url
    foreach ($extractedUrls as $extractedUrl /* @var $extractedUrl Url */) {
      $this->repository->insert($extractedUrl);
    }

    // log
    if ($error === 0) {
      $this->logger->info($message);
    } else {
      $this->logger->error($message);
    }

    // mark Url as processed
    $this->repository->done($url);
  }

  // <editor-fold defaultstate="collapsed" desc="accessors">

  public function getMaxTasks()
  {
    return $this->maxTasks;
  }

  public function setMaxTasks($maxTasks)
  {
    $this->maxTasks = $maxTasks;
  }

  public function getServers()
  {
    return $this->servers;
  }

  public function setServers($servers)
  {
    $this->servers = $servers;
  }

  public function getFunctionName()
  {
    return $this->functionName;
  }

  public function setFunctionName($functionName)
  {
    $this->functionName = $functionName;
  }

  public function getRepository()
  {
    return $this->repository;
  }

  public function setRepository(RepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function setLogger(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }

  // </editor-fold>
}