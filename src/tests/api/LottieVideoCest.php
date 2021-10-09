<?php

use Codeception\Util\HttpCode;

class LottieVideoCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    /**
     * @target 默认搜索
     */
    public function testSearch(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/lottie-videos',
            [
                'keyword' => ''
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'lottie_video_search',
                'message' => 'Lottie Video Search'
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
     * @target 无搜索词搜索
     * @classId: 1
     * @page: 1
     */
    public function testSearchClassIdOfOnePageOfOne(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/lottie-videos',
            [
                'keyword' => '',
                'class_id' => 1,
                'page' => 1
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'lottie_video_search',
                'message' => 'Lottie Video Search'
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
     * @target 有搜索词：可爱
     * @classId: 1
     * @page: 1
     */
    public function testSearchCarryKeywordClassIdOfOnePageOfOne(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/lottie-videos',
            [
                'keyword' => '可爱',
                'class_id' => [],
                'page' => 1
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'lottie_video_search',
                'message' => 'Lottie Video Search'
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
     * @taget 无搜索关键词
     * @classId: 3
     * @page: 1
     */
    public function testSearchClassIdOfThreePageOfOne(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/lottie-videos',
            [
                'keyword' => '',
                'class_id' => [3],
                'page' => 1
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'lottie_video_search',
                'message' => 'Lottie Video Search'
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
