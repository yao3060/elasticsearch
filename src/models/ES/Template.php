<?php

namespace app\models\ES;

use app\components\IpsAuthority;
use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\ESTemplate;
use app\models\Templtaglink;
use app\models\Test;
use Yii;
use yii\elasticsearch\Query;

class Template extends BaseModel
{
    public static $redis_db = "_search";

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
                    'created' => ['type' => 'date', "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd", 'include_in_all' => false],
                    'updated' => ['type' => 'date', "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd", 'include_in_all' => false],
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

    public static function getMapping()
    {
        $redis_key = "ES_template:mapping";
        $return = Tools::getRedis(self::$redis_db, $redis_key);

        if ($return) return $return;
        $db = static::getDb();
        $command = $db->createCommand();
        $return = $command->getMapping(static::index(), static::type(), static::mapping());
        Tools::setRedis(self::$redis_db, $redis_key, $return, 3600);
        return $return;
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
        $command->createIndex(static::index(), [
            'settings' => ["number_of_shards" => 5, "number_of_replicas" => 0],//5个分片0个复制
            'mappings' => static::mapping(),
            //'warmers' => [ /* ... */ ],
            //'aliases' => [ /* ... */ ],
            //'creation_date' => '...'
        ]);
    }

    public function attributes()
    {
        return ['id', 'temple_id', 'title', 'description', 'brief', 'created', 'kid_1', 'kid_2', 'kid_3', 'pr', 'man_pr', 'man_pr_add', 'tag_id', 'is_zb', 'hot_keyword', 'keyword_show_edit', 'edit', 'updated', 'ratio', 'class_sort', 'info', 'template_type', 'hide_description', 'hide_in_ios', 'class_id', 'web_dl'
            , 'week_web_dl', 'month_web_dl', 'total_web_dl', 'width', 'height'];
    }

    /**
     * 搜索
     * @param QueryBuilderInterface $query
     * @return array
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();

        $return = [];

        $reStartTime = microtime(true);
        if (!IpsAuthority::check(IOS_ALBUM_USER)) {
            $return = Tools::getRedis(self::$redis_db, $redisKey);
        }
        $redisStat['st'] = (int)((microtime(true) - $reStartTime) * 1000);
        $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($dbt[1]['function']) ? $dbt[1]['function'] : null;
        $redisStat['key'] = 'ES_template12-23:' . $caller . ($query->fuzzy == 1 ? 1 : null);
        $redisStat['hit'] = 1;

        $baseQuery = $query->query();

        if (!$return || Tools::isReturnSource() || $query->update == 1) {
            // 把回源从不命中中去除
            if (!$return || !$return['total']) {
                $redisStat['hit'] = 0;
            }
            $reStartTime = microtime(true);
//            Yii::$app->redis9->incr("search_return_source_incr");
            unset($return);
            $costInfo = [];
            $costInfo['created'] = date('Y-m-d H:i:s', time());
            $esStartTime = microtime(true);
            $err = 0;
            try {
                if (!empty($query->color)) {
                    $flg = '_col';
                    $info = (new Query())->from('818ps_pic', '818ps_pic')
                        ->source(['templ_id'])
                        ->query($baseQuery)
                        ->offset($query->offset)
                        ->limit($query->pageSize)
                        ->createCommand($query->elasticsearchColor)
                        ->search(['timeout' => '5s'], ['track_scores' => true])['hits'];
                } else {
                    $flg = '';
                    $info = self::find()
                        ->source(['temple_id'])
                        ->query($baseQuery)
                        ->orderBy($query->sort)
                        ->offset($query->offset)
                        ->limit($query->pageSize)
                        ->createCommand()
                        ->search(['timeout' => '5s'], ['track_scores' => true])['hits'];
                }
                $costInfo['err'] = 0;
            } catch (\exception $e) {
                $err = 1;
                Test::sqltest('searchKeywordFalse', $e->getMessage(), $query->keyword);
                $info['hit'] = 0;
                $info['total'] = -1;
                $info['ids'] = [];
                $info['score'] = [];
                $costInfo['err'] = 1;
            }

            $esQueryTime = microtime(true) - $esStartTime;
            $costInfo['cost_time'] = $esQueryTime;
            $ip = Tools::getClientIP();
            $costInfo['name'] = 'Template' . $flg . $ip . $query->getRedisKey();
            $info_redis = 'redis_search';
//            Yii::$app->$info_redis->Rpush('ES_query_time:query_time', json_encode($costInfo));

            $return['total'] = $info['total'];
            $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
            foreach ($info['hits'] as $value) {
                $return['ids'][] = $value['_id'];
                $return['score'][$value['_id']] = $value['sort'][0];
            }
            if (!IpsAuthority::check(IOS_ALBUM_USER) && $err == 0) {
                $expireTime = $info['total'] == 0 ? 7200 + rand(0, 3600) : 126000 + rand(-3600, 7200);
//                $res = Tools::setRedis(self::$redis_db, $query->getRedisKey(), $return, $expireTime); // 35小时过期
//                if (!$res) {
//                    Test::sqltest('setsearchKeywordRedis', $res, $query->getRedisKey());
//                }
            }
            $redisStat['pt'] = (int)((microtime(true) - $reStartTime) * 1000);
        }

        $redisStat['created'] = date('Y-m-d H:i:s', time());
//        Yii::$app->redis_monitor->Rpush('redis_stat:', json_encode($redisStat));

        return $return;
    }

    /**
     * 推荐搜索
     * @param QueryBuilderInterface $query
     * @return array
     */
    public function recommendSearch(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();
        $return = Tools::getRedis(self::$redis_db, $redisKey);

        if (empty($return) || Tools::isReturnSource()) {
            $costInfo = [];
            $esStartTime = microtime(true);
            try {
                $info = self::find()
                    ->source(['temple_id'])
                    ->query($query->query())
                    //                ->orderBy($sort)
                    ->offset(($query->page - 1) * $query->pageSize)
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search(['timeout' => '5s'], ['track_scores' => true])['hits'];
                $costInfo['err'] = 0;
            } catch (\exception $e) {
                $info['hit'] = 0;
                $info['ids'] = [];
                $info['score'] = [];
                $costInfo['err'] = 1;
            }

            $esQueryTime = microtime(true) - $esStartTime;
            $costInfo['created'] = date('Y-m-d H:i:s', time());
            $costInfo['cost_time'] = $esQueryTime;
            $costInfo['name'] = 'Template' . '_recommend';
//            $info_redis = 'redis_search';
//            Yii::$app->$info_redis->Rpush('ES_query_time:query_time', json_encode($costInfo));

            $return = [];
            $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
            foreach ($info['hits'] as $value) {
                $return['ids'][] = $value['_id'];
                $return['score'][$value['_id']] = isset($value['sort'][0]) ?? [];
            }
//            Tools::setRedis(self::$redis_db, $redisKey, $return);
        }

        return $return;
    }

    public static function getEsTableName()
    {
        $redis = 'redis_search';
        $es_name = Yii::$app->$redis->get('es_table_name');
        if (!$es_name) {
//        if (!$es_name || Tools::isReturnSourceVisitor()) {
            $es_name = TableName::find()->select(['es_name'])->where(['is_use' => 1])->one()['es_name'];
            Tools::setRedis(self::$redis_db, 'es_table_name', $es_name);
        }
        return $es_name;
    }

    public static function sortDefault($keyword, $class_id = [], $index_name = null)
    {
        $index_name = !empty($index_name) ? $index_name : self::getEsTableName();
        //        $source = "doc['pr'].value-doc['man_pr'].value+doc['man_pr_add'].value";
        if ($class_id && is_array($class_id) == false) {
            $class_id = explode('_', $class_id);
        }
        $source = "doc['pr'].value+(int)(_score*10)";
        if (strstr($keyword, 'h5') || strstr($keyword, 'H5')) {
            $source .= "+10000-((int)(doc['template_type'].value-5)*(int)(doc['template_type'].value-5)*400)";
        }

        if ($keyword) {
            //关键词人工pr
            $mapping = Template::getMapping();
            $hot_keyword = [];
            foreach ($mapping[$index_name]['mappings']['list']['properties']['hot_keyword']['properties'] as $kk => $property) {
                if (isset($property['type']) && $property['type'] == 'long') {
                    $hot_keyword[] = (string)$kk;
                }
            }

            if (in_array((string)$keyword, $hot_keyword, true)) {
                $source .= "+doc['hot_keyword.{$keyword}'].value";
            }

            // 根据展示点击率调整pr
            //            $optimize_keyword = array_keys($mapping[$index_name']['mappings']['list']['properties']['keyword_show_edit']['properties']);
            //            $optimize_keyword = explode('!!!', implode('!!!', $optimize_keyword));//强制转换为string类型
            //            if (in_array((string)$keyword, $optimize_keyword)) {
            //                $source .= "+doc['keyword_show_edit.{$keyword}'].value";
            //            }

        } elseif ($class_id && count($class_id) >= 1) {
            //标签的人工pr
            $choose_class_id = 0;
            foreach ($class_id as $v) {
                if ($v > 0 || $v == -1) {
                    $choose_class_id = $v;
                }
            }
            if ($choose_class_id > 0 || $choose_class_id == -1) {
                $mapping = Template::getMapping();
                $class_sort = array_keys($mapping[$index_name]['mappings']['list']['properties']['class_sort']['properties']);
                $class_sort = explode('!!!', implode('!!!', $class_sort));//强制转换为string类型
                if (in_array((string)$choose_class_id, $class_sort)) {
                    $source .= "+doc['class_sort.{$choose_class_id}'].value";
                }
            }
        }
        $sort['_script'] = [
            'type' => 'number',
            'script' => [
                "lang" => "painless",
                "source" => $source
            ],
            'order' => 'desc'
        ];
        return $sort;
    }

    public static function sortByHot()
    {
        return 'edit desc';
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

    public static function sortByTime()
    {
        return 'created desc';
    }

    public static function sortByYesday()
    {
        return 'web_dl desc';
    }

    public static function sortByWeekday()
    {
        return 'week_web_dl desc';
    }

    public static function sortByMonth()
    {
        return 'month_web_dl desc';
    }
}
