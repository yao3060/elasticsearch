<?php

use Codeception\Util\HttpCode;

class TemplateCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-stagging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    public function testSortTypeByTime(ApiTester $I)
    {
        $I->sendGet(API_TESTING_BASE_URL . 'v1/templates', [
            'keyword' => '',
            'page' => 1,
            'kid1' => 0,
            'kid2' => 0,
            'sort_type' => 'bytime',
            'is_zb' => 1,
            'ratio' => '',
            'class_id' => '',
            'template_type' => 3,
            'update' => 0,
            'use' => 0,
            'page_size' => 35
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'es_template_search',
            'message' => 'ESTemplate Search'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'total' => 'integer',
               'hit' => 'integer',
               'ids' => 'array',
               'score' => 'array'
           ]
       ]);
    }

    public function testPageOfTwoCarryClassId(ApiTester $I)
    {
        $I->sendGet(API_TESTING_BASE_URL . 'v1/templates', [
            'keyword' => '',
            'page' => 2,
            'kid1' => 0,
            'kid2' => 0,
            'sort_type' => '',
            'is_zb' => 1,
            'ratio' => '',
            'class_id' => '290_0_0',
            'template_type' => 3,
            'update' => 0,
            'use' => 0,
            'page_size' => 35
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'es_template_search',
            'message' => 'ESTemplate Search'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'total' => 'integer',
               'hit' => 'integer',
               'ids' => 'array',
               'score' => 'array'
           ]
       ]);
    }

    public function testCarryClassIdTagId(ApiTester $I)
    {
        $I->sendGet(API_TESTING_BASE_URL . 'v1/templates', [
            'keyword' => '',
            'page' => 2,
            'kid1' => 0,
            'kid2' => 0,
            'tag_id' => 46,
            'sort_type' => '',
            'is_zb' => 1,
            'ratio' => '',
            'class_id' => '290_334_0_0',
            'template_type' => 3,
            'update' => 0,
            'use' => 0,
            'page_size' => 35
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'es_template_search',
            'message' => 'ESTemplate Search'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'total' => 'integer',
               'hit' => 'integer',
               'ids' => 'array',
               'score' => 'array'
           ]
       ]);
    }

    public function testCarryClassIdTagIdSortType(ApiTester $I)
    {
        $I->sendGet(API_TESTING_BASE_URL . 'v1/templates', [
            'keyword' => '',
            'page' => 2,
            'kid1' => 0,
            'kid2' => 0,
            'tag_id' => 46,
            'sort_type' => 'bytime',
            'is_zb' => 1,
            'ratio' => '',
            'class_id' => '290_334_0_0',
            'template_type' => 3,
            'update' => 0,
            'use' => 0,
            'page_size' => 35
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'es_template_search',
            'message' => 'ESTemplate Search'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'total' => 'integer',
               'hit' => 'integer',
               'ids' => 'array',
               'score' => 'array'
           ]
       ]);
    }

    public function testCarryKeyword(ApiTester $I)
    {
        $I->sendGet(API_TESTING_BASE_URL . 'v1/templates', [
            'keyword' => '你好',
            'page' => 1,
            'kid1' => 0,
            'kid2' => 0,
            'tag_id' => 46,
            'sort_type' => 'bytime',
            'is_zb' => 1,
            'ratio' => '',
            'class_id' => '10_30_0',
            'template_type' => 1,
            'update' => 0,
            'use' => 0,
            'page_size' => 32,
            'width' => 1242,
            'height' => 2208,
            'class_intersection_search' => 1
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'es_template_search',
            'message' => 'ESTemplate Search'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'total' => 'integer',
               'hit' => 'integer',
               'ids' => 'array',
               'score' => 'array'
           ]
       ]);
    }

    public function testSearchCarryKeywordClassIdsSortTypeTagId(ApiTester $I)
    {
        $I->sendGet(API_TESTING_BASE_URL . 'v1/templates', [
            'keyword' => '环保',
            'page' => 1,
            'kid1' => 0,
            'kid2' => 0,
            'tag_id' => 46,
            'sort_type' => '',
            'is_zb' => 1,
            'ratio' => '',
            'class_id' => '290_0_0_0',
            'template_type' => 1,
            'update' => 0,
            'use' => 0,
            'page_size' => 32
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'es_template_search',
            'message' => 'ESTemplate Search'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'total' => 'integer',
               'hit' => 'integer',
               'ids' => 'array',
               'score' => 'array'
           ]
       ]);
    }

    public function testSearchCarryKeywordClassIdsTagId(ApiTester $I)
    {
        $I->sendGet(API_TESTING_BASE_URL . 'v1/templates', [
            'keyword' => '环保',
            'page' => 1,
            'kid1' => 0,
            'kid2' => 0,
            'tag_id' => 49,
            'sort_type' => '',
            'is_zb' => 1,
            'ratio' => '',
            'class_id' => '290_0_0_710',
            'template_type' => 3,
            'update' => 0,
            'use' => 0,
            'page_size' => 35
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'es_template_search',
            'message' => 'ESTemplate Search'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'total' => 'integer',
               'hit' => 'integer',
               'ids' => 'array',
               'score' => 'array'
           ]
       ]);
    }

    public function testSearchCarryKeywordClassIdsTagIdSecond(ApiTester $I)
    {
        $I->sendGet(API_TESTING_BASE_URL . 'v1/templates', [
            'keyword' => '环保',
            'page' => 1,
            'kid1' => 0,
            'kid2' => 0,
            'tag_id' => 104,
            'sort_type' => '',
            'is_zb' => 1,
            'ratio' => '',
            'class_id' => '290_0_0_710',
            'template_type' => 3,
            'update' => 0,
            'use' => 0,
            'page_size' => 35
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'es_template_search',
            'message' => 'ESTemplate Search'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'total' => 'integer',
               'hit' => 'integer',
               'ids' => 'array',
               'score' => 'array'
           ]
       ]);
    }
}
