<?php
namespace app\tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\SensitiveWord;
use app\queries\ES\SensitiveWordSearchQuery;
use Codeception\Test\Unit;
use GuzzleHttp\Client;

class SensitiveWordTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    public function prepareData(
        $keyword = '',
        $prodUrl = ''
    )
    {
        $validate = (new SensitiveWord())->search(new SensitiveWordSearchQuery(
            keyword: $keyword
        ));

        var_dump($validate);
        exit;

        $response = (new Client())->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(),  true);

        $flag = $responseJson['msg'];

        return [
            'dev' => $validate['flag'] ? '空' : '',
            'prod' => $flag
        ];
    }

    /**
     * @target 违规搜索词：党政
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
