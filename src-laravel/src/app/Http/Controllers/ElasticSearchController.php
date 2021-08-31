<?php

namespace App\Http\Controllers;

use App\Services\ElasticSearch\Models\Asset;
use Illuminate\Http\Request;


class ElasticSearchController extends Controller
{
    public function stats()
    {
        return (new Asset())->stats();
    }
    public function query(Request $request)
    {
        return $request->all();
    }
}
