<?php

namespace App\Services\ElasticSearch\Models;

use App\Services\ElasticSearch\Contracts\ElasticSearchInterface;
use  Cviebrock\LaravelElasticsearch\Facade as Elasticsearch;
// use Elasticsearch\Client;

abstract class Model implements ElasticSearchInterface
{
    public $connection;

    function __construct()
    {
        $this->connection = Elasticsearch::connection('default');
    }
}
