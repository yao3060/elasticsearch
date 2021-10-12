<?php

class CreateUserCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    // tests
    public function tryToTest(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet('/');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'welcome']);
        $I->seeResponseMatchesJsonType([
            'code' => 'string',
            'message' => 'string',
            'data' => 'array',
        ]);
    }
}
