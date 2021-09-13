<?php
namespace app\tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\SensitiveWord;
use app\queries\ES\SensitiveWordSearchQuery;
use GuzzleHttp\Client;

class SensitiveWordTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    protected function _after()
    {
    }

    public function prepareData(
        $keyword = '',
        $prodUrl = ''
    )
    {
        $validate = (new SensitiveWord())->search(new SensitiveWordSearchQuery(
            keyword: $keyword
        ));

        $response = (new Client())->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(),  true);

        $flag = $responseJson['msg'];

        return [
            'dev' => $validate['flag'] ? '空' : '',
            'prod' => $flag
        ];
    }

    /**
     * 违禁词搜索验证
     */
    public function testValidateSensitiveWord()
    {
        $compare = $this->prepareData(
            keyword: '党政',
            prodUrl: getenv('UNIT_BASE_URL') . '/api/get-template-list?w=' .urlencode('党政')
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }
}
