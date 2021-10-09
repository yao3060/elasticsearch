<?php

use Codeception\Util\HttpCode;

class DesignerTemplateCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    public function testKid1OfOneKid2OfNineteen(ApiTester $I)
    {
        $I->sendGet(API_TESTING_BASE_URL . 'v1/designer-templates', [
            'keyword' => '',
            'page' => 1,
            'kid1' => 1,
            'kid2' => 19,
            'sort_type' => '',
            'is_zb' => 0,
            'ratio' => '',
            'class_id' => '',
            'template_type' => 1,
            'use' => 0,
            'page_size' => 35
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'design_template_index',
            'message' => 'DesignTemplateIndex'
        ]);
        $I->seeResponseMatchesJsonType([
            'code' => 'string',
            'message' => 'string',
            'data' => [
                'total' => 'integer',
                'hit' => 'integer',
                'ids' => 'array',
                'score' => 'array'
            ]
        ]);
    }

    public function testCarryKeyword(ApiTester $I)
    {
        $I->sendGet(API_TESTING_BASE_URL . 'v1/designer-templates', [
            'keyword' => '主图',
            'page' => 1,
            'kid1' => 156,
            'kid2' => 301,
            'sort_type' => 'default',
            'is_zb' => 1,
            'ratio' => '',
            'class_id' => '0_0_0_0',
            'template_type' => 4,
            'templ_info' => [
                "picId" => ""
            ],
            'use' => 0,
            'page_size' => 10000
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'design_template_index',
            'message' => 'DesignTemplateIndex'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'total' => 'integer',
               'hit' => 'integer',
               'ids' => 'array',
               'score' => 'array'
           ]
       ]);
    }
}
