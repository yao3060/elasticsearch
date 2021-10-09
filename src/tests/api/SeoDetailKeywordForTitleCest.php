<?php

use \Codeception\Util\HttpCode;

class SeoDetailKeywordForTitle
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
    public function testGetSeoDetailKeyword(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/seo/title-keywords',
            [
                'keyword' => "你好",
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_seo_detail_keyword_for_title_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'id' => 'integer',
                    'keyword' => 'string',
                ],
            ]
        );
    }
    /**
     * @param ApiTester $I
     * 无关键词测试
     */
    public function testGetSeoDetailKeywordNull(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/seo/title-keywords',
            [
                'keyword' => '',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_seo_detail_keyword_for_title_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'id' => 'integer',
                    'keyword' => 'string',
                ],
            ]
        );
    }
}
