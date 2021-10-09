<?php

use \Codeception\Util\HttpCode;

class VideoAudioCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }
    // tests
    public function testGetVideoAudio(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/audiovisuals',
            [
                'keyword' => "你好",
                'page' => 1,
                'page_size' => 30,
                'parents_id' => 0,
                'class_id' => 0,
                'prep' => 0,
                'is_designer' => 0,
                'is_vip' => 0,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_videoAudio_list']);
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

    public function testGetVideoAudioNull(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/audiovisuals',
            [
                'keyword' => "你好",
                'page' => 1,
                'page_size' => 30,
                'parents_id' => 0,
                'class_id' => 0,
                'prep' => 0,
                'is_designer' => 0,
                'is_vip' => 0,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_videoAudio_list']);
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
