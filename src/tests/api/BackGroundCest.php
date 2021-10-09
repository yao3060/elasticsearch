<?php

use \Codeception\Util\HttpCode;

class BackGroundCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    /**
     * @param ApiTester $I
     * 有关键词测试
     */
    public function testGetBackGround(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/backgrounds',
            [
                'keyword' => "你好",
                'page' => 1,
                'page_size' => 40,
                'scene_id' => 0,
                'is_zb' => 0,
                'sort' => 0,
                'use_count' => 0,
                'kid' => 0,
                'ratio_id' => 0,
                'class' => 0,
                'is_bg' => 0,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_background_list']);
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
    public function testGetBackGroundKeyNull(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/backgrounds',
            [
                'keyword' => '',
                'page' => 1,
                'page_size' => 40,
                'scene_id' => 0,
                'is_zb' => 0,
                'sort' => 0,
                'use_count' => 0,
                'kid' => 0,
                'ratio_id' => 0,
                'class' => 0,
                'is_bg' => 0,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_background_list']);
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
     * 更改is_zb测试
     */
    public function testGetBackGroundSaveIsZb(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/backgrounds',
            [
                'keyword' => '',
                'page' => 1,
                'page_size' => 40,
                'scene_id' => 0,
                'is_zb' => 1,
                'sort' => 0,
                'use_count' => 0,
                'kid' => 0,
                'ratio_id' => 0,
                'class' => 0,
                'is_bg' => 0,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_background_list']);
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
