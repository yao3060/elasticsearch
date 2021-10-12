<?php

use Codeception\Util\HttpCode;

class VideoTemplateCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    /**
     * @target 默认，无搜索词搜索
     * @tips 其余参数默认
     */
    public function testSearch(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/video-templates',
            [
                'keyword' => '',
                'page_size' => 32
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'video_template_search',
                'message' => 'Video Template Search'
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
     * @target 有搜索词：教师节
     * @ratio: 2
     */
    public function testSearchCarryKeyword(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/video-templates',
            [
                'keyword' => '教师节',
                'page_size' => 32,
                'ratio' => 2
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'video_template_search',
                'message' => 'Video Template Search'
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
     * @target 无搜索词
     * @classId: [1579, 1580]
     * @page: 2
     */
    public function testSearchCarryClassIdsPageOfTwo(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/video-templates',
            [
                'keyword' => '',
                'page' => 2,
                'page_size' => 32,
                'ratio' => 1,
                'class_id' => [1579, 1580]
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'video_template_search',
                'message' => 'Video Template Search'
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
     * @target 有搜索词：丢失
     * @classId: []
     */
    public function testSearchCarryKeywordClassIdsOfNone(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/video-templates',
            [
                'keyword' => '丢失',
                'page' => 1,
                'ratio' => 1
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'video_template_search',
                'message' => 'Video Template Search'
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
