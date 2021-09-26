<?php
namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\H5SensitiveWords;
use app\queries\ES\H5SensitiveWordsSearchQuery;
use GuzzleHttp\Client;
use yii\helpers\ArrayHelper;


class H5BanWordsTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \GuzzleHttp\Client
     */
    private $http;

    protected function _before()
    {
        IpsAuthority::definedAuth(); // 初始化权限变量
        $this->http = new \GuzzleHttp\Client();
    }

    protected function _after()
    {
    }

    // tests
    /*public function testSearchOne()
    {
        $items = (new H5SensitiveWords())
            ->checkBanWord(new H5SensitiveWordsSearchQuery(
                         word: '去你妈'
                     ));
        $prodUrl = getenv('UNIT_BASE_URL') .'/h5-api/prohibited-words';
        $response = (new Client())->post($prodUrl, [
            'word' => ['word' => '去你妈']
        ]);
        $content = json_decode($response->getBody()->getContents());
    }*/

}
