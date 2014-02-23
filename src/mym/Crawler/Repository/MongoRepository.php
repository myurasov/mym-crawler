<?php

namespace mym\Crawler\Repository;

use mym\Crawler\Url;
use mym\Crawler\Repository\RepositoryInterface;

class MongoRepository implements RepositoryInterface
{
  private $server = '';
  private $database = '';
  private $collection = '';

  /**
   * @var \MongoCollection
   */
  private $mongoCollection;

  /**
   * Maximum link depth
   */
  private $maxDepth = 5;

  /**
   * Minumum age of urls to be processes since last update [sec].
   * -1 equals infinity (do not process again).
   */
  private $minAgeToReprocess = -1;

  public function __construct($server = 'mongodb://localhost:27017', $db = 'Crawler', $collection = 'repository')
  {
    $this->server = $server;
    $this->database = $db;
    $this->collection = $collection;
  }

  /**
   * @return \MongoCollection
   */
  private function getMongoCollection()
  {
    // connect to the database
    if (!$this->mongoCollection) {
      $mongoClient = new \MongoClient($this->server);
      $this->mongoCollection = $mongoClient->selectCollection($this->database, $this->collection);
    }

    return $this->mongoCollection;
  }

  private function createIndexes()
  {
    static $indexesCreated = false;

    if (!$indexesCreated) {
      $this->getMongoCollection()->ensureIndex([
        'url.updatedAt' => 1 /* ASC */
      ]);

      $this->getMongoCollection()->ensureIndex([
        'url.createdAt' => 1 /* ASC */
      ]);

      $this->getMongoCollection()->ensureIndex([
        'url.depth' => 1
      ]);

      $this->getMongoCollection()->ensureIndex([
        'url.status' => 1
      ]);

      $indexesCreated = true;
    }
  }

  private function createNextQuery()
  {
    $query = [
      'url.depth' => ['$lte' => $this->maxDepth],
      'processing' => false,
    ];

    // min age sice last update
    if (-1 === $this->minAgeToReprocess) {
      $query['url.status'] = Url::STATUS_NEW;
    } else {
      $query['$or'] = [
        ['url.updatedAt' => ['$lte' => microtime(true) - $this->minAgeToReprocess]],
        ['url.status' => Url::STATUS_NEW]
      ];
    }

    return $query;
  }

  public function get($id)
  {
    $data = $this->getMongoCollection()->findOne([
      '_id' => $id
    ]);

    if (!empty($data)) {
      $url = new Url();
      $url->fromArray($data['url']);
      return $url;
    }

    return false;
  }

  /**
   * @return Url
   */
  public function next()
  {
    $data = $this->getMongoCollection()->findAndModify($this->createNextQuery(), [
      '$set' => ['processing' => true]
    ], null, ['sort' => ['url.updatedAt' => 1]]);

    if (!empty($data)) {
      $url = new Url();
      $url->fromArray($data['url']);
      return $url;
    }

    return false;
  }

  public function count()
  {
    return $this->getMongoCollection()->count($this->createNextQuery());
  }

  public function resetProcessing()
  {
    $this->getMongoCollection()->update([
      'processing' => true
    ], [
      '$set' => ['processing' => false]
    ], [
      'multiple' => true
    ]);
  }

  public function resetStatus($status)
  {
    $this->getMongoCollection()->update([
      'url.status' => $status
    ], [
      '$set' => ['url.status' => Url::STATUS_NEW]
    ], [
      'multiple' => true
    ]);
  }

  public function clear()
  {
    $this->getMongoCollection()->drop();
  }

  public function remove($key)
  {
    $this->getMongoCollection()->remove(['_id' => $key]);
  }

  public function update(Url &$url)
  {
    $url->refreshUpdatedAt();

    $this->getMongoCollection()->update([
      '_id' => $url->getId(),
    ], [
      'url' => $url->toArray()
    ]);
  }

  /**
   * Mark Url as done
   */
  public function done(Url &$url)
  {
    $url->refreshUpdatedAt();

    $this->getMongoCollection()->findAndModify([
      '_id' => $url->getId(),
    ], ['$set' => [
      'url' => $url->toArray(),
      'processing' => false
    ]]);
  }

  public function insert(Url $url)
  {
    try {
      $this->getMongoCollection()->insert([
        '_id' => $url->getId(),
        'processing' => false,
        'url' => $url->toArray()
      ]);
    }
    catch (\MongoCursorException $e) {

      if ($e->getCode() === 11000 /* Duplicate */) {
        return false;
      }

      throw $e;
    }

    $this->createIndexes();

    return true;
  }

  // <editor-fold defaultstate="collapsed" desc="accessors">

  public function setServer($server)
  {
    $this->server = $server;
  }

  public function setDatabase($database)
  {
    $this->database = $database;
  }

  public function setCollection($collection)
  {
    $this->collection = $collection;
  }

  public function getMaxDepth()
  {
    return $this->maxDepth;
  }

  public function setMaxDepth($maxDepth)
  {
    $this->maxDepth = $maxDepth;
  }

  public function getMinAgeToReprocess()
  {
    return $this->minAgeToReprocess;
  }

  public function setMinAgeToReprocess($minAgeToReprocess)
  {
    $this->minAgeToReprocess = $minAgeToReprocess;
  }

  // </editor-fold>
}