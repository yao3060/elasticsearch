<?php

use Codeception\Util\HttpCode;

class RichEditorAssetCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    /**
     * @target: 默认，无搜索条件
     */
    public function testSearchDefault(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/rich-editor-assets',
            [
                'keyword' => ''
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'rich_editor_asset_search',
                'message' => 'Rich Editor Asset Search'
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
     * @target: 默认，无搜索条件
     * @classId: [1, 0]
     */
    public function testSearchClassIds(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/rich-editor-assets',
            [
                'keyword' => '',
                'class_id' => [1, 0],
                'page' => 1,
                'page_size' => 40
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'rich_editor_asset_search',
                'message' => 'Rich Editor Asset Search'
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
     * @target: 默认，无搜索条件
     * @classId: [2, 55]
     */
    public function testSearchClassIdsSecond(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/rich-editor-assets',
            [
                'keyword' => '',
                'class_id' => [2, 55],
                'page' => 1,
                'page_size' => 40
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'rich_editor_asset_search',
                'message' => 'Rich Editor Asset Search'
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
     * @target: 默认，无搜索条件
     * @classId: [2, 55]
     * @page: 2
     */
    public function testSearchClassIdsPage(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/rich-editor-assets',
            [
                'keyword' => '',
                'class_id' => [1, 0],
                'page' => 3
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'rich_editor_asset_search',
                'message' => 'Rich Editor Asset Search'
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
     * @target 有搜索词：橘色
     * @classId: [5, 58]
     */
    public function testSearchCarryKeywordClassIds(ApiTester $I)
    {
        $I->sendGet(
            API_TESTING_BASE_URL.'v1/rich-editor-assets',
            [
                'keyword' => '橘色',
                'class_id' => [5, 58],
                'page' => 1
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'code' => 'rich_editor_asset_search',
                'message' => 'Rich Editor Asset Search'
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
