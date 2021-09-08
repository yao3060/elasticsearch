<?php

namespace app\models\ES;

use app\components\IpsAuthority;
use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
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

    public function search(QueryBuilderInterface $query): array
    {
        $costInfo = [];
        $costInfo['created'] = date('Y-m-d H:i:s', time());
        $esStartTime = microtime(true);
        $err = 0;

        $returnQuery = $query->query();

        if ($returnQuery['return']) {
            return $returnQuery['return'];
        }

        try {
            if ($query->color) {
                $flg = '_col';
                $info = (new Query())->from('818ps_pic', '818ps_pic')
                    ->source(['templ_id'])
                    ->query($returnQuery['query'])
                    ->offset($returnQuery['offset'])
                    ->limit($query->pageSize)
                    ->createCommand($query->elasticsearchColor)
                    ->search(['timeout' => '5s'], ['track_scores' => true])['hits'];
            } else {
                $flg = '';
                $info = self::find()
                    ->source(['temple_id'])
                    ->query($returnQuery['query'])
                    ->orderBy($returnQuery['sort'])
                    ->offset($returnQuery['offset'])
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search(['timeout' => '5s'], ['track_scores' => true])['hits'];
            }
            $costInfo['err'] = 0;
        } catch (\Exception $exception) {
            $err = 1;
            Test::sqltest('searchKeywordFalse', $exception->getMessage(), $query->keywords);
            $info['hit'] = 0;
            $info['total'] = -1;
            $info['ids'] = [];
            $info['score'] = [];
            $costInfo['err'] = 1;
        }

        $esQueryTime = microtime(true) - $esStartTime;
        $costInfo['cost_time'] = $esQueryTime;
        $ip = Tools::getClientIP();
        $costInfo['name'] = 'Template' . $flg . $ip . $returnQuery['redisKey'];
        $info_redis = 'redis_search';
        Yii::$app->$info_redis->Rpush('ES_query_time:query_time', json_encode($costInfo));

        $return['total'] = $info['total'];
        $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
        foreach ($info['hits'] as $value) {
            $return['ids'][] = $value['_id'];
            $return['score'][$value['_id']] = $value['sort'][0];
        }

        if (!IpsAuthority::check(IOS_ALBUM_USER) && $err == 0) {
            $expire_time = $info['total'] == 0 ? 7200 + rand(0, 3600) : 126000 + rand(-3600, 7200);
            $res = Tools::setRedis(self::$redis_db, $returnQuery['redisKey'], $return, $expire_time); // 35小时过期
            if (!$res) {
                Test::sqltest('setsearchKeywordRedis', $res, $returnQuery['redisKey']);
            }
        }

        $redis_stat['pt'] = (int)((microtime(true) - $returnQuery['reStartTime']) * 1000);
        $redis_stat['created'] = date('Y-m-d H:i:s', time());
        Yii::$app->redis_monitor->Rpush('redis_stat:', json_encode($redis_stat));

        return $return;
    }

    public function recommendSearch(QueryBuilderInterface $query): array
    {
        $costInfo = [];
        $esStartTime = microtime(true);
        $returnQuery = $query->query();
        try {
            $info = Template::find()
                ->source(['temple_id'])
                ->query($returnQuery['query'])
                //                ->orderBy($sort)
                ->offset(($query->page - 1) * $query->pageSize)
                ->limit($query->pageSize)
                ->createCommand()
                ->search(['timeout' => '5s'], ['track_scores' => true])['hits'];
            $return = [];
            $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
            foreach ($info['hits'] as $value) {
                $return['ids'][] = $value['_id'];
                $return['score'][$value['_id']] = $value['sort'][0] ?? '';
            }

            Tools::setRedis(self::$redis_db, $returnQuery['key'], $return);

            $costInfo['err'] = 0;
        } catch (\exception $e) {
            $info['hit'] = 0;
            $info['ids'] = [];
            $info['score'] = [];
            $costInfo['err'] = 1;
        }

        $es_query_time = microtime(true) - $esStartTime;
        $costInfo['created'] = date('Y-m-d H:i:s', time());
        $costInfo['cost_time'] = $es_query_time;
        $costInfo['name'] = 'Template' . '_recommend';
        $info_redis = 'redis_search';
        Yii::$app->$info_redis->Rpush('ES_query_time:query_time', json_encode($costInfo));

        return $return;
    }

    public static function queryKeyword($keyword, $is_or = false)
    {
        $operator = $is_or ? 'or' : 'and';
        $fields = ["title^16", "description^2", "hide_description^2", "brief^2", "info^1"];
        if ($operator == 'or') {
            $keyword = str_replace(['图片'], '', $keyword);
            $fields = ["title^16", "description^2", "hide_description^2", "info^1"];
        }
        if (in_array($keyword, ['LOGO', 'logo'])) {
            $fields = ["title^16", "description^2", "hide_description^2", "info^1"];
        }
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => $fields,
            'type' => 'most_fields',
            "operator" => $operator
        ];
        return $query;
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

    /**
     * @param array $hex_colors 十六进制颜色值
     * @param array $weights 颜色值对应的权重值 (0, 1]
     * @param int $e 颜色搜索范围
     * @return array
     */
    public static function formatColor($hex_colors, $weights = [100], $e = 50)
    {
        if (count($hex_colors) == 1) {
            $e = 80;
        }
        $colors = [];
        foreach ($hex_colors as $key => $value) {
            $current_w = $weights[$key] / 100 ?? 1;
            $r = hexdec(substr($value, 0, 2));
            $g = hexdec(substr($value, 2, 2));
            $b = hexdec(substr($value, 4, 2));
            array_push($colors, $r, $g, $b, $current_w);
        }

        $colorRange = [];
        $color = self::getColorFeature($colors);
        foreach ($color as $c) {
            $min = $c - $e >= 0 ? $c - $e : 0;
            $max = $c + $e <= 255 ? $c + $e : 255;
            array_push($colorRange, ['from' => $min, 'to' => $max]);
        }
        $colorParams = self::getColorField($colors);//color->params->center

        return [$colorRange, $colorParams];
    }

    /**
     * 函数名称：获取颜色特征值 缩小搜索区域
     * 输入形如 [128,186,200,0.2,
     * 58,110,85,0.7,
     * 214,28,59,0.1]
     *  输出形如 [87,117,105]
     */
    private static function getColorFeature($colors)
    {
        $r = $g = $b = $w = 0;
        for ($i = 0; $i < count($colors) / 4; ++$i) {
            $offset = 4 * $i;
            $current_w = $colors[$offset + 3];
            $r += $colors[$offset] * $current_w;
            $g += $colors[$offset + 1] * $current_w;
            $b += $colors[$offset + 2] * $current_w;
            $w += $current_w;
        }
        if ($w == 0) {
            return [0, 0, 0];
        } else {
            return [0 => $r / $w, 1 => $g / $w, 2 => $b / $w];
        }
    }

    /**
     * 进行颜色编码
     * 入参 必选 形如 [128,186,200,0.2, 58,110,85,0.7,214,28,59,0.1]
     */
    public static function getColorField($colors)
    {
        $str = "";
        for ($i = 0; $i < count($colors) / 4; ++$i) {
            $offset = 4 * $i;
            $current_w = $colors[$offset + 3];
            $r = sprintf("%02x", $colors[$offset] & 0xff);
            $g = sprintf("%02x", $colors[$offset + 1] & 0xff);
            $b = sprintf("%02x", $colors[$offset + 2] & 0xff);
            $w = sprintf("%02x", (int)($current_w * 100));
            $str = $str . $r . $g . $b . $w . "_";
        }

        return substr($str, 0, strlen($str) - 1);
    }

    public static function sortByHot()
    {
        return 'edit desc';
    }

    public function rules()
    {
        return [
//            [
//                [
//                    'keywords', 'kid1', 'kid2', 'sortType', 'tagId', 'isZb', 'page', 'pageSize',
//                    'ratio', 'classId', 'update', 'fuzzy', 'templateType', 'color', 'use', 'width', 'height',
//                    'classIntersectionSearch'
//                ],
//                'required'
//            ],
            [['keyword'], 'string'],
//            [
//                [
//                    'kid1', 'kid2', 'tagId', 'ratio', 'page', 'pageSize', 'classId', 'fuzzy', 'templateType', 'width', 'height',
//                    'classIntersectionSearch'
//                ],
//                'integer'
//            ],
//            [['isZb', 'update'], 'boolean']
        ];
    }

    public function recommendRules()
    {
        return [
            [['keyword'], 'string']
        ];
    }
}
