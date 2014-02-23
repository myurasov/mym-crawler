<?php

/**
 * @copyright 2013, Mikhail Yurasov <me@yurasov.me>
 */

namespace mym\Crawler;

use mym\Crawler\Repository\RepositoryInterface;
use Psr\Log\LoggerAwareInterface;

interface DispatcherInterface extends LoggerAwareInterface
{
  public function run();
  public function getRepository();
  public function setRepository(RepositoryInterface $repository);
}