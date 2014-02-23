<?php

namespace mym\Crawler\Repository;

use mym\Crawler\Url;

interface RepositoryInterface
{
  /**
   * Sets processing flag to true
   *
   * @return Url|false
   */
  public function next();

  /**
   * Get Url by id
   * @param string $id
   * @return Url
   */
  public function get($id);

  public function count();

  public function resetProcessing();

  /**
   * Set urls status to Url::STATUS_NEW insted of $status
   * @param string $status
   */
  public function resetStatus($status);

  public function clear();

  public function update(Url &$url);

  /**
   * Sets processing flag to false
   */
  public function done(Url &$url);

  public function insert(Url $url);

  public function getMaxDepth();

  public function setMaxDepth($maxDepth);

  public function getMinAgeToReprocess();

  public function setMinAgeToReprocess($minAgeToReprocess);
}
