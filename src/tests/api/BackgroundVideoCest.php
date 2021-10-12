<?php

use Codeception\Util\HttpCode;

class BackgroundVideoCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    /**
     * @target 无搜索词，默认搜索条件
     * @video video controller 无关键词搜索
     */
    public function testSearch(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/background-videos',
            [
                'keyword' => '',
                'class_id' => [4],
                'page_size' => 40
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'background_video_search',
                'message' => 'Background Video Search'
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
     * @target 有搜索词，比例为 1
     * @video 关键词搜索：插画
     * @ratio 1 比例
     */
    public function testVideoSearch(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/background-videos',
            [
                'keyword' => '',
                'page_size' => 40
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'background_video_search',
                'message' => 'Background Video Search'
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
     * @target 有搜索词，比例为 2
     * @video video controller 关键词搜索：商务
     * @ratio 2 比例
     */
    public function testVideoSearchCarryKeyword(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/background-videos',
            [
                'keyword' => '插画',
                'page' => 1,
                'page_size' => 30,
                'class_id' => 0,
                'ratio' => 1
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'background_video_search',
                'message' => 'Background Video Search'
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

    public function testVideoSearchCarryKeywordBusiness(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/background-videos',
            [
                'keyword' => '商务',
                'page' => 1,
                'page_size' => 30,
                'class_id' => 0,
                'ratio' => 2
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'background_video_search',
                'message' => 'Background Video Search'
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
     * @target 有搜索词，页码自定义
     * @video video controller 关键词搜索：邀请函
     * @ratio 2 比例
     * @page 3 页码
     */
    public function testVideoSearchCarryKeywordInvitation(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/background-videos',
            [
                'keyword' => '邀请函',
                'page' => 3,
                'page_size' => 30,
                'class_id' => 0,
                'ratio' => 2
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'background_video_search',
                'message' => 'Background Video Search'
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
     * @target 测试无搜索词分页自定义
     * @video video controller
     * @ratio 2 比例
     * @page 1 页码
     * @pageSize 9 每页展示数量
     */
    public function testVideoSearchPageOfNine(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/background-videos',
            [
                'keyword' => '',
                'page' => 1,
                'page_size' => 9,
                'ratio' => 2
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'background_video_search',
                'message' => 'Background Video Search'
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
