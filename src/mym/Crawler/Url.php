<?php

namespace mym\Crawler;

class Url
{
  const STATUS_NEW = 'new';
  const STATUS_OK = 'ok';
  const STATUS_ERROR = 'error';
  const STATUS_REJECTED = 'rejected'; // no processor accepts the url

  private $url = '';
  private $depth = 0;
  private $status = self::STATUS_NEW;
  private $metadata = []; // additional data associated with url (type, etc)

  private $createdAt;
  private $updatedAt = null;

  public function __construct($url = '', $depth = 0)
  {
    $this->createdAt = $this->updatedAt = microtime(true);
    $this->setUrl($url);
    $this->setDepth($depth);
  }

  public function toArray()
  {
    return array_intersect_key(get_object_vars($this), [
      // keys to expose
      'url' => 1,
      'depth' => 1,
      'status' => 1,
      'createdAt' => 1,
      'updatedAt' => 1,
      'metadata' => 1
    ]);
  }

  public function fromArray(array $data)
  {
    $data = array_intersect_key($data, [
      // keys to use
      'url' => 1,
      'depth' => 1,
      'status' => 1,
      'createdAt' => 1,
      'updatedAt' => 1,
      'metadata' => 1
    ]);

    foreach ($data as $k => $v) {
      $this->$k = $v;
    }
  }

  /**
   * Get url unique id
   * @return string
   */
  public function getId()
  {
    return md5($this->url);
  }

  public function refreshUpdatedAt()
  {
    $this->updatedAt = microtime(true);
  }

  /**
   * @param array|string $metadata
   * @param mixed $value
   */
  public function setMetadata($metadata)
  {
    if (is_array($metadata)) {
      $this->metadata = array_merge($this->metadata, $metadata);
    } else if (is_string($metadata)) {
      $value = func_get_arg(1);
      $this->metadata[$metadata] = $value;
    }
  }

  public function getMetadata($key = null)
  {
    if (is_null($key)) {
      return $this->metadata;
    } else if (isset($this->metadata[$key])) {
      return $this->metadata[$key];
    } else {
      return false;
    }
  }

  // <editor-fold defaultstate="collapsed" desc="accessors">

  public function getUrl()
  {
    return $this->url;
  }

  public function setUrl($url)
  {
    $this->url = $url;
  }

  public function getDepth()
  {
    return $this->depth;
  }

  public function setDepth($depth)
  {
    $this->depth = $depth;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function setStatus($status)
  {
    $this->status = $status;
  }

  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  public function setCreatedAt($createdAt)
  {
    $this->createdAt = $createdAt;
  }

  public function getUpdatedAt()
  {
    return $this->updatedAt;
  }

  public function setUpdatedAt($updatedAt)
  {
    $this->updatedAt = $updatedAt;
  }

  // </editor-fold>
}