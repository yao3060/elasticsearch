<?php

use Codeception\Util\HttpCode;

class SensitiveWordCest
{
    public function _before(ApiTester $I)
    {
    }

    public function testValidateSensitiveWordPartyPolicy(ApiTester $I)
    {
        $I->sendPost(API_TESTING_BASE_URL . 'v1/sensitive-words/validate', [
            'keyword' => '党政'
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'sensitive_word_validate',
            'message' => 'Sensitive Word Validate'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'flag' => 'boolean'
           ]
       ]);
    }

    public function testValidateSensitiveWordMaoZeDong(ApiTester $I)
    {
        $I->sendPost(API_TESTING_BASE_URL . 'v1/sensitive-words/validate', [
            'keyword' => '毛泽东'
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'sensitive_word_validate',
            'message' => 'Sensitive Word Validate'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'flag' => 'boolean'
           ]
       ]);
    }
}
