<?php

use Codeception\Util\HttpCode;

class LottieVideoWordCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    /**
     * @target 默认，无搜索条件
     */
    public function testSearch(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/lottie-video-words',
            [
                'keyword' => '',
                'page' => 1,
                'page_size' => 40
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'lottie_video_word_search',
                'message' => 'Lottie Video Word Search'
            ]
        );
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'hit' => 'integer',
                    'ids' => 'array',
                    'score' => 'array'
                ]
            ]
        );
    }

    /**
     * @target 搜索词：风景
     */
    public function testSearchCarryKeyword(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/lottie-video-words',
            [
                'keyword' => '风景',
                'page' => 1,
                'page_size' => 40
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'lottie_video_word_search',
                'message' => 'Lottie Video Word Search'
            ]
        );
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'hit' => 'integer',
                    'ids' => 'array',
                    'score' => 'array'
                ]
            ]
        );
    }
}
