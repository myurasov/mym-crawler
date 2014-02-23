<?php

namespace AppBase\Crawler;

use mym\Crawler\Processor\ProcessorInterface;
use mym\Crawler\Url;
use Symfony\Component\DomCrawler\Link;

class TestProcessor implements ProcessorInterface
{
  /**
   * @var Url[]
   */
  protected $extractedUrls = [];

  public function process(Url &$url)
  {
    $this->extractedUrls = [];

    $client = new \Goutte\Client();

    $crawler = $client->request('GET', $url->getUrl());

    $url->setStatus(
      $client->getResponse()->getStatus() >= 400
      ? Url::STATUS_ERROR
      : Url::STATUS_OK
    );

    $links = $crawler->filter('a');

    $links = $crawler->filter('a')->links();

    foreach ($links as $link /* @var $link Link */) {
      $eu = new Url($link->getUri(), $url->getDepth() + 1);

      if (preg_match('#^http(s)?://fleapop.com#i', $eu->getUrl())) {
        $this->extractedUrls[] = $eu;
      }

    }

    //

    return true;
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
