<?php

namespace mym\Crawler\Processor;

use mym\Crawler\Url;

/**
 * Processor interface
 */
interface ProcessorInterface
{
  /**
   * @param Url $url Url to handle
   * @return boolean False if Url cannot be processed
   */
  public function process(Url &$url);
  public function getExtractedUrls();
  public function setExtractedUrls($extractedUrls);
}