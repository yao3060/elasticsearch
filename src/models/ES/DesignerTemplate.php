<?php

namespace app\models\ES;

use app\components\IpsAuthority;
use app\components\Tools;
use app\models\TaskTemplateLink;
use app\models\Templ;
use app\queries\ES\DesignerTemplateSearchQuery;
use Yii;
use app\interfaces\ES\QueryBuilderInterface;
use yii\elasticsearch\Query;

class DesignerTemplate extends BaseModel
{
    const REDIS_DB = '_search';

    public static $occupyNum = 10;
    //二次设计redisKey
    public static $occupyKey = "task:template:link:occupy:old:tid:uid:"; // 占用被驳回
    public static $hashKey = "is:second:hash:template:id"; // 占用的模板 用不过期
    public static $designRedisDb = 8;

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

                if (isset($info['hits']) && sizeof($info['hits'])) {
                    $total = $info['total'] ?? 0;
                    $return['total'] = $total;
                    $return['hit'] = $total > 10000 ? 10000 : $total;
                    foreach ($info['hits'] as $value) {
                        $return['ids'][] = $value['_id'];
                        $return['score'][$value['_id']] = $value['sort'][0];
                    }
                } else {
                    $return = [
                        'hit' => 0,
                        'ids' => [],
                        'score' => []
                    ];
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

//        if (!IpsAuthority::check(IOS_ALBUM_USER)) {
//            Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, 86400 + rand(-3600, 3600));
//        }

        return $return;
    }

    /**
     * 长尾词新增二次创作功能  需要筛选出精品s级的作品 http://redmine.818ps.com/issues/7346
     * @param int $keyword
     * @param int $page
     * @param int $kid1
     * @param int $kid2
     * @param string $sortType
     * @param int $tagId
     * @param int $isZb
     * @param int $pageSize
     * @param null $ratio
     * @param int $classId
     * @param int $update
     * @param int $size
     * @param int $fuzzy
     * @param int[] $templateType
     * @param array $templInfo
     * @param array $color
     * @param int $use
     * @return array
     */
    public function getTemplateIds($keyword = 0, $page = 1, $kid1 = 0, $kid2 = 0, $sortType = 'default', $tagId = 0, $isZb = 1, $pageSize = 100, $ratio = null, $classId = 0, $update = 0, $size = 0, $fuzzy = 0, $templateTypes = [1, 2], $templInfo = [], $color = [], $use = 0)
    {
        //获取页数
        $p = $page_num = $page;
        $queryBuilder = new DesignerTemplateSearchQuery(
            keyword: $keyword,
            page: $page,
            kid1: $kid1,
            kid2: $kid2,
            sortType: $sortType,
            tagId: $tagId,
            isZb: $isZb,
            pageSize: $pageSize,
            ratio: $ratio,
            classId: $classId,
            update: $update,
            size: $size,
            fuzzy: $fuzzy,
            templateTypes: $templateTypes,
            templateInfo: $templInfo,
            color: $color,
            use: $use
        );
        $page = $queryBuilder->getRedisKey();
        //获取结果集
        if (isset($templInfo['type']) && $templInfo['type']) unset($templInfo['type']);
//        var_dump([
//            'keyword' => $keyword,
//            'page' => $page,
//            'kid1' => $kid1,
//            'kid2' => $kid2,
//            'sort_type' => $sortType,
//            'tag_id' => $tagId,
//            'is_zb' => $isZb,
//            'page_size' => $pageSize,
//            'ratio' => $ratio,
//            'class_id' => $classId,
//            'size' => $size,
//            'fuzzy' => $fuzzy,
//            'template_type' => $templateTypes,
//            'templ_info' => $templInfo,
//            'color' => $color,
//            'use' => $use,
//        ]);exit;
        $templIdArr = self::search($queryBuilder);

        $ids = $templIdArr['ids'];

//        var_dump([
//            'ids' => count($ids),
//            'occupy' => self::$occupyNum
//        ]);exit;

        //如果总数少于最小限制则返回false
        if (count($ids) < self::$occupyNum) {
            $templIdArr['ids'] = self::delTemplId($templIdArr['ids'], self::$hashKey);
            return $templIdArr;
        }
        $hit = $templIdArr['hit'];
        $offset = 32;
        $levels = array();
        //小于10个继续查，同时要给前端返回查询es的页数
        while (count($levels) < self::$occupyNum) {
            $num = ($p - 1) * $offset;
            $tmpAds = array_splice($ids, $num, $offset);
            //小于10个则需要翻页
            if (count($tmpAds) < self::$occupyNum) {
                $page = $page + 1;
                //当前页已全部占满  以后无需再此页搜索
//                Yii::$app->redis8->hset(self::$hashKeySecondPage, self::$esKey, $page);
                $templIdArr = self::search($queryBuilder);
                //翻页后如数量不足  则终止循环
                if (count($templIdArr['ids']) < self::$occupyNum) {
                    $ids = array_merge($ids, $templIdArr['ids']);
                    $p = 2; //返回第二页
                    $levelArr = self::delTemplId($ids, self::$hashKey);
                    $levels = array_merge($levels, $levelArr);
                    break;
                }
                $ids = $templIdArr['ids'];
                $num = 0;
                $p = 1;
                $tmpAds = array_splice($ids, $num, $offset);
            }
            //筛选符合条件的模板id
            $levelArr = self::delTemplId($tmpAds, self::$hashKey);
            $levels = array_merge($levels, $levelArr);
            $p += 1;
        }
        $templIdArr = $levels;
        $picId = $templInfo['picId'] ?? "";
        //将已占用的模板放在数组的第一条
        if (!empty($picId)) {
            $uid = Yii::$app->user->id;
            $occupyKey = self::$occupyKey . $picId . ":" . $uid;
            $occupy = Tools::getRedis(self::$designRedisDb, $occupyKey);
            if ($occupy === false) {
                $occupy = TaskTemplateLink::find()->alias('k')
                    ->leftJoin(Templ::tableName() . ' t', 'k.templ_id=t.id')
                    ->Where(['=', 'k.templ_id', $picId])
                    ->andWhere(['=', 'k.is_second', '1'])
                    ->andWhere(['=', 'k.is_occupy', '1'])
                    ->andWhere(['=', 'k.status', '2'])  //2代表被驳回
                    ->andWhere(['=', 't.author', $uid])
                    ->andWhere(['=', 't.audit_through', 1])
                    ->select('k.old_templ_id')
                    ->one()['old_templ_id'];
                if (!empty($occupy)) {
                    Tools::setRedis(self::$designRedisDb, $occupyKey, $occupy, 300);
                }
            }
            if (!empty($occupy) && $occupy != -1 && $page_num == 1) {
                array_unshift($templIdArr, $occupy);
                $templIdArr = array_unique($templIdArr);
            } elseif (empty($occupy)) {
                Tools::setRedis(self::$designRedisDb, $occupyKey, -1, 300);
            }
        }
        $templIdArr = array_splice($templIdArr, 0);
        return ['hit' => $hit, 'ids' => $templIdArr, 'page' => $p, 'occupy' => $occupy ?? 0];
    }

    /**
     * 判断hash表里是否存在，存在则说明已被占用,需要剔除
     * @param $templIdArr
     * @return mixed
     */
    public static function delTemplId($templIdArr, $hashKey)
    {
        foreach ($templIdArr as $k => $v) {
            $hash = Yii::$app->redis8->hexists($hashKey, $v);
            if ($hash) {
                unset($templIdArr[$k]);
            }
        }
        return $templIdArr;
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
        $redisKey = "ES_template:mapping:second";
        $return = Tools::getRedis(self::REDIS_DB, $redisKey);

        if ($return) return $return;
        $db = static::getDb();
        $command = $db->createCommand();
        $return = $command->getMapping(static::index(), static::type(), static::mapping());
        Tools::setRedis(self::REDIS_DB, $redisKey, $return, 3600);
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
