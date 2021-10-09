<?php

use \Codeception\Util\HttpCode;

class PptTemplateCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    // tests
    public function testGetPptTemplate(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/ppt-templates',
            [
                'class_id' => '10',
                'page' => 1,
                'page_size' => 30,
                'class_level2_ids' => 0,
                'class_level3_ids' => 0,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_template_single_page_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'total'=>'integer',
                    'hit' => 'integer',
                    'ids' => 'array',
                    'score' => 'array',
                ],
            ]
        );
    }

    public function testGetPptTemplateNull(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/ppt-templates',
            [
                'class_id' => '15',
                'page' => 1,
                'page_size' => 30,
                'class_level2_ids' => 0,
                'class_level3_ids' => 0,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_template_single_page_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'total'=>'integer',
                    'hit' => 'integer',
                    'ids' => 'array',
                    'score' => 'array',
                ],
            ]
        );
    }
}
