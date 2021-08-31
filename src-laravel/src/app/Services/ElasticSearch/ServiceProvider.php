<?php

namespace App\Services\ElasticSearch;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Cviebrock\LaravelElasticsearch\Facade as Elasticsearch;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Elasticsearch\Client::class, function ($app) {
            $name = $app['config']['elasticsearch.defaultConnection'];
            $name = 'color';
            return Elasticsearch::connection($name);
        });
    }
}
