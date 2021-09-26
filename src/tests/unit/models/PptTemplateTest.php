<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\PptTemplate;
use app\queries\ES\PptTemplateSearchQuery;
use yii\helpers\ArrayHelper;

class PptTemplateTest extends \Codeception\Test\Unit
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

    // tests
    public function testSearchOne()
    {
        $items = (new PptTemplate())
            ->search(
                new PptTemplateSearchQuery(
                    c1: 10,
                )
            );
        $response = $this->http->post(
            getenv('UNIT_BASE_URL') . '/apiv2/sp-search',
            [
                'json' => [
                    'class' => '10',
                ]
            ]
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->data->list, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }

}
