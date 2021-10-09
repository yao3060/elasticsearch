<?php

use \Codeception\Util\HttpCode;

class PictureCest
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
    public function testGetPicture(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/pictures',
            [
                'keyword' => "你好",
                'page' => 1,
                'page_size' => 30,
                'scene_id' => 0,
                'is_zb' => 1,
                'kid' => 1,
                'vip_pic' => 1,
                'ratio_id' => 1,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_picture_list']);
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
    public function testGetPictureNull(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/pictures',
            [
                'keyword' => '',
                'page' => 1,
                'page_size' => 30,
                'scene_id' => 0,
                'is_zb' => 1,
                'kid' => 1,
                'vip_pic' => 1,
                'ratio_id' => 1,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_picture_list']);
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
