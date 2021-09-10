<?php

namespace app\models\ES;

use app\components\IpsAuthority;
use app\components\Tools;
use Yii;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;
use yii\elasticsearch\Query;

class DesignerTemplate extends BaseModel
{
    const REDIS_DB = '_search';

    public static function getDb()
    {
        return Yii::$app->get('elasticsearch_second');
    }

    public function search(QueryBuilderInterface $query): array
    {

        try {

            $return = null;
            $redisKey = $query->getRedisKey();

            if (!IpsAuthority::check(IOS_ALBUM_USER)) {
                $return = Tools::getRedis(self::REDIS_DB, $redisKey);
            }

            $return = false;

            if (!$return || !$return['total'] || Tools::isReturnSource() || $query->update == 1) {

                if (!empty($query->color)) {

                    $info = (new Query())->from('818ps_pic', '818ps_pic')
                        ->source(['templ_id'])
                        ->query($query->query())
                        ->offset($query->offset)
                        ->limit($query->pageSize)
                        ->createCommand(Yii::$app->get('elasticsearch_color'))
                        ->search([], ['track_scores' => true])['hits'];

                } else {
                    $info = self::find()
                        ->source(['temple_id'])
                        ->query($query->query())
                        ->orderBy($query->sort)
                        ->offset($query->offset)
                        ->limit($query->pageSize)
                        ->createCommand()
                        ->search([], ['track_scores' => true])['hits'];

                }

                $return['total'] = $info['total'];
                $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
                foreach ($info['hits'] as $value) {
                    $return['ids'][] = $value['_id'];
                    $return['score'][$value['_id']] = $value['sort'][0];
                }
            }
        } catch (\Exception $e) {
            $return = [];
            $return['hit'] = 0;
            $return['ids'] = [];
            $return['score'] = [];
            $errInfo = [
                'loadTime' => date("Y-m-d h:i:s"),
                'errorMsg' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ];
            Yii::error(http_build_query($errInfo));
        }

        if (!IpsAuthority::check(IOS_ALBUM_USER)) {
            Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, 86400 + rand(-3600, 3600));
        }

        return $return;
    }

    public static function sortByTime()
    {
        return 'created desc';
    }

    public static function index()
    {
        return 'second_design';
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
                    'author_type' => ['type' => 'byte', 'include_in_all' => false],
                    'templ_attr' => ['type' => 'byte', 'include_in_all' => false],
                    'settlement_level' => ['type' => 'byte', 'include_in_all' => false],
                    'main_color' => ['type' => 'text', 'include_in_all' => false],
                    'price' => ['type' => 'float', 'include_in_all' => false],
                    'is_occupy' => ['type' => 'byte', 'include_in_all' => false],
                    'class_id' => ['type' => 'long', 'include_in_all' => false],
                    'format_id' => ['type' => 'integer', 'include_in_all' => false],
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

    public static function getMapping()
    {
        $redis_key = "ES_template:mapping:second";
        $return = Tools::getRedis(self::REDIS_DB, $redis_key);

        if ($return) return $return;
        $db = static::getDb();
        $command = $db->createCommand();
        $return = $command->getMapping(static::index(), static::type(), static::mapping());
        Tools::setRedis(self::REDIS_DB, $redis_key, $return, 3600);
        return $return;
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
}
