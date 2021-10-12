<?php

use \Codeception\Util\HttpCode;

class AlbumCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    /**
     * @param ApiTester $I
     * 有关键词
     */
    public function testGetAlbum(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/albums',
            [
                'keyword' => "你好",
                'page' => 3,
                'page_size' => 30,
                'class_id' => 0,
                'type' => 1,
                'sort_type' => 'default',
                'update' => 0,
                'fuzzy' => 0,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_album_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'hit' => 'integer',
                    'ids' => 'array',
                    'score' => 'array',
                ],
            ]
        );
    }

    /**
     * @param ApiTester $I
     * 更改type
     */
    public function testGetAlbumSaveType(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/albums',
            [
                'keyword' => "你好",
                'page' => 1,
                'page_size' => 30,
                'class_id' => 0,
                'type' => 0,
                'sort_type' => 'default',
                'update' => 0,
                'fuzzy' => 0,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_album_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'hit' => 'integer',
                    'ids' => 'array',
                    'score' => 'array',
                ],
            ]
        );
    }

    /**
     * @param ApiTester $I
     * 无关键词测试
     */
    public function testGetAlbumKeyNull(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/albums',
            [
                'keyword' => '',
                'page' => 3,
                'page_size' => 30,
                'class_id' => 0,
                'type' => 1,
                'sort_type' => 'default',
                'update' => 0,
                'fuzzy' => 0,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_album_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'hit' => 'integer',
                    'ids' => 'array',
                    'score' => 'array',
                ],
            ]
        );
    }
}
