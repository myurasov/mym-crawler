<?php

namespace mym\Crawler\Processor;

use mym\Crawler\Url;
use Goutte\Client as GoutteClient;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AbstractProcessor implements ProcessorInterface
{
  /**
   * @var Url[]
   */
  protected $extractedUrls = [];

  /**
   *
   * @var GoutteClient
   */
  private $client;

  /**
   * @return GoutteClient
   */
  protected function getWebClient()
  {
    if (!$this->client) {
      $this->client = new GoutteClient();
      $this->client->setServerParameter('HTTP_USER_AGENT', 'Crawler');
    }

    return $this->client;
  }

  /**
   * @param Url $url
   * @return Crawler
   */
  protected function crawlUrl(&$url)
  {
    $client = $this->getWebClient();
    $uri = $url->getUrl();

    try {
      $crawler = $client->request('GET', $uri);

      $status = $client->getResponse()->getStatus();

      if ($status >= 400) {
        throw new HttpException($status);
      }

    } catch (\Exception $e) {
      $url->setStatus(Url::STATUS_ERROR);
      throw $e;
    }

    $url->setStatus(Url::STATUS_OK);

    return $crawler;
  }

  public function process(Url &$url)
  {
  }

  public function getExtractedUrls()
  {
    return $this->extractedUrls;
  }

  public function setExtractedUrls($extractedUrls)
  {
    $this->extractedUrls = $extractedUrls;
  }
}