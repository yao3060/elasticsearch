<?php

namespace app\models\ES;

use app\components\IpsAuthority;
use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\Backend\TableName;
use app\models\Backend\Templtaglink;
use app\models\Backend\Test;
use Yii;
use yii\elasticsearch\Query;
use yii\base\Exception;

class Template extends BaseModel
{
    public static $redisDb = "_search";

    public static function getMapping()
    {
        $redis_key = "ES_template:mapping";
        $return = Tools::getRedis(self::$redisDb, $redis_key);

        if ($return) {
            return $return;
        }
        $db = static::getDb();
        $command = $db->createCommand();
        $return = $command->getMapping(static::index(), static::type(), static::mapping());
        Tools::setRedis(self::$redisDb, $redis_key, $return, 3600);
        return $return;
    }

    public static function index()
    {
        return 'template1';
    }

    public static function type()
    {
        return 'list';
    }

    public static function mapping()
    {
        return [
            static::type() => [
                "dynamic" => true,
                'properties' => [
                    'temple_id' => ['type' => 'integer', 'include_in_all' => false],
                    'title' => ['type' => 'text', 'analyzer' => "ik_smart", 'include_in_all' => true],
                    'description' => ['type' => 'text', 'analyzer' => "ik_smart", 'include_in_all' => true],
                    'hide_description' => ['type' => 'text', 'analyzer' => "ik_smart", 'include_in_all' => true],
                    'brief' => ['type' => 'text', 'analyzer' => "ik_smart", 'include_in_all' => true],
                    'created' => [
                        'type' => 'date',
                        "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd",
                        'include_in_all' => false
                    ],
                    'updated' => [
                        'type' => 'date',
                        "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd",
                        'include_in_all' => false
                    ],
                    'kid_1' => ['type' => 'integer', 'include_in_all' => false],
                    'kid_2' => ['type' => 'integer', 'include_in_all' => false],
                    'kid_3' => ['type' => 'integer', 'include_in_all' => false],
                    'pr' => ['type' => 'long', 'include_in_all' => false],
                    'man_pr' => ['type' => 'long', 'include_in_all' => false],
                    'man_pr_add' => ['type' => 'long', 'include_in_all' => false],
                    'tag_id' => ['type' => 'integer', 'include_in_all' => false],
                    'is_zb' => ['type' => 'short', 'include_in_all' => false],
                    'hot_keyword' => ['type' => 'object'],//格式为关键词:分数
                    'keyword_show_edit' => ['type' => 'object'],//格式为关键词:分数
                    'edit' => ['type' => 'long', 'include_in_all' => false],
                    'ratio' => ['type' => 'short', 'include_in_all' => false],
                    'class_sort' => ['type' => 'object'],//格式为分类:分数
                    'info' => ['type' => 'text', 'analyzer' => "ik_smart", 'include_in_all' => true],
                    'template_type' => ['type' => 'integer', 'include_in_all' => false],
                    'hide_in_ios' => ['type' => 'short', 'include_in_all' => false],
                    'web_dl' => ['type' => 'long', 'include_in_all' => false],
                    'week_web_dl' => ['type' => 'long', 'include_in_all' => false],
                    'month_web_dl' => ['type' => 'long', 'include_in_all' => false],
                    'total_web_dl' => ['type' => 'long', 'include_in_all' => false],
                    'width' => ['type' => 'long', 'include_in_all' => false],
                    'height' => ['type' => 'long', 'include_in_all' => false],
                ]
            ],
        ];
    }

    public static function updateMapping()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->setMapping(static::index(), static::type(), static::mapping());
    }

    public static function saveRecord($fields = [])
    {
        $info = self::findOne($fields['id']);
        if (!$info) {
            $info = new self();
            $info->primaryKey = $fields['id'];
        }
        $info->temple_id = $fields['id'];
        $info->title = $fields['title'];
        $info->description = $fields['description'];
        $info->hide_description = $fields['hide_description'];
        $info->brief = $fields['brief'];
        $info->created = $fields['created'];
        $info->kid_1 = $fields['kid_1'];
        $info->kid_2 = $fields['kid_2'];
        $info->kid_3 = $fields['kid_3'];
        $info->pr = $fields['pr'] ? $fields['pr'] : 0;
        $info->man_pr = $fields['man_pr'] ? $fields['man_pr'] : 0;
        $info->man_pr_add = $fields['man_pr_add'] ? $fields['man_pr_add'] : 0;
        $info->tag_id = $fields['tag_id'] ? $fields['tag_id'] : 0;
        $info->is_zb = $fields['is_zb'] ? $fields['is_zb'] : 0;
        $info->edit = $fields['edit'] ? $fields['edit'] : 0;
        $info->updated = date('Y-m-d H:i:s');
        $tag = [];
        $find_tags = Templtaglink::find()->select('tag_id')->where(['template_id' => $fields['id']])->all();
        foreach ($find_tags as $key => $value) {
            $tag[] = $value['tag_id'];
        }
        $info->tag_id = $tag;
        $info->template_type = $fields['template_type'];
        $info->hide_in_ios = $fields['hide_in_ios'] ? $fields['hide_in_ios'] : 0;
        $info->save();
    }

    /**
     * Create this model's index
     */
    public static function createIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->createIndex(
            static::index(),
            [
                'settings' => ["number_of_shards" => 5, "number_of_replicas" => 0],//5个分片0个复制
                'mappings' => static::mapping(),
                //'warmers' => [ /* ... */ ],
                //'aliases' => [ /* ... */ ],
                //'creation_date' => '...'
            ]
        );
    }

    public static function getEsTableName()
    {
        $redis = 'redis_search';
        $es_name = Yii::$app->$redis->get('es_table_name');
        if (!$es_name || Tools::isReturnSourceVisitor()) {
            $es_name = TableName::find()->select(['es_name'])->where(['is_use' => 1])->one()['es_name'];

            Tools::setRedis(self::$redisDb, 'es_table_name', $es_name);
        }
        return $es_name;
    }

    public function attributes()
    {
        return [
            'id',
            'temple_id',
            'title',
            'description',
            'brief',
            'created',
            'kid_1',
            'kid_2',
            'kid_3',
            'pr',
            'man_pr',
            'man_pr_add',
            'tag_id',
            'is_zb',
            'hot_keyword',
            'keyword_show_edit',
            'edit',
            'updated',
            'ratio',
            'class_sort',
            'info',
            'template_type',
            'hide_description',
            'hide_in_ios',
            'class_id',
            'web_dl'
            ,
            'week_web_dl',
            'month_web_dl',
            'total_web_dl',
            'width',
            'height'
        ];
    }

    /**
     * 搜索
     * @param  \app\queries\ES\TemplateSearchQuery  $query
     * @return array
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();
        \Yii::info("[Template:redisKey]:[$redisKey]", __METHOD__);

        $response = [];

        if (!IpsAuthority::check(IOS_ALBUM_USER)) {
            $response = Tools::getRedis(self::$redisDb, $redisKey);
        }

        if (!empty($response) && isset($response['hit']) && $response['hit'] && Tools::isReturnSource() === false) {
            \Yii::info('template search data source from redis', __METHOD__);
            return $response;
        }

        Yii::$app->redis9->incr("search_return_source_incr");

        $return = [
            'total' => 0,
            'hit' => 0,
            'ids' => [],
            'score' => []
        ];

        try {
            if ($query->hasColor()) {
                $info = (new Query())->from('818ps_pic', '818ps_pic')
                    ->source(['templ_id'])
                    ->query($query->query())
                    ->offset($query->offset)
                    ->limit($query->pageSize)
                    ->createCommand($query->elasticsearchColor)
                    ->search(['timeout' => '5s'], ['track_scores' => true])['hits'];
            } else {
                $info = self::find()
                    ->source(['temple_id'])
                    ->query($query->query())
                    ->orderBy($query->sort)
                    ->offset($query->offset)
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search(['timeout' => '5s'], ['track_scores' => true])['hits'];
            }

            if (isset($info['hits']) && $info['hits']) {
                $return['total'] = $info['total'] ?? 0;
                $return['hit'] = $return['total'] > 10000 ? 10000 : $return['total'];

                foreach ($info['hits'] as $value) {
                    $return['ids'][] = $value['_id'] ?? 0;
                    $return['score'][$value['_id']] = $value['sort'][0] ?? [];
                }
            }

            if (!IpsAuthority::check(IOS_ALBUM_USER)) {
                $expireTime = $info['total'] == 0 ? 7200 + rand(0, 3600) : 126000 + rand(-3600, 7200);
                $res = Tools::setRedis(self::$redisDb, $query->getRedisKey(), $return, $expireTime); // 35小时过期
                if (!$res) {
                    Test::sqltest('setsearchKeywordRedis', $res, $query->getRedisKey());
                }
            }
        } catch (Exception $e) {
            Test::sqltest('searchKeywordFalse', $e->getMessage(), $query->keyword);
            throw new Exception($e->getMessage());
        }

        return $return;
    }

    /**
     * 推荐搜索
     * @param  QueryBuilderInterface  $query
     * @return array
     */
    public function recommendSearch(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();
        $return = Tools::getRedis(self::$redisDb, $redisKey);
        \Yii::info("[TemplateRecommendSearch:redisKey]:[$redisKey]", __METHOD__);

        if (!empty($return) && isset($return['hit']) && $return['hit'] && Tools::isReturnSource() === false) {
            \Yii::info('template recommend search data source from redis', __METHOD__);
            return $return;
        }

        $responseData = [
            'hit' => 0,
            'ids' => [],
            'score' => []
        ];

        try {
            $info = self::find()
                ->source(['temple_id'])
                ->query($query->query())
                //                ->orderBy($sort)
                ->offset(($query->page - 1) * $query->pageSize)
                ->limit($query->pageSize)
                ->createCommand()
                ->search(['timeout' => '5s'], ['track_scores' => true])['hits'];

            if (isset($info['hits']) && $info['hits']) {
                $total = $info['total'] ?? 0;
                $responseData['hit'] = $total > 10000 ? 10000 : $total;
                foreach ($info['hits'] as $value) {
                    $responseData['ids'][] = $value['_id'] ?? 0;
                    $responseData['score'][$value['_id']] = isset($value['sort'][0]) ?? [];
                }
            }
        } catch (\Exception $e) {
            \Yii::error("Template Model Error: " . $e->getMessage(), __METHOD__);
        }

        Tools::setRedis(self::$redisDb, $redisKey, $responseData);

        return $responseData;
    }

    public function rules()
    {
        return [
            [['keyword'], 'string'],
        ];
    }

    public function recommendRules()
    {
        return [
            [['keyword'], 'string']
        ];
    }
}
