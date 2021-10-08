<?php


namespace app\models\ES;


use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;

/**
 * 片段视频
 * Class VideoTemplate
 * @package app\models\ES
 */
class VideoTemplate extends BaseModel
{
    public static $redisDb = "_search";

    public static function index()
    {
        return 'video_excerpt';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return [
            'temple_id',
            'title',
            'class_id',
            'description',
            'hide_description',
            'brief',
            'created',
            'updated',
            'info'
        ];
    }

    /**
     * @param  \app\queries\ES\VideoTemplateSearchQuery  $query
     * @return array|false
     * @throws Exception
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();
        \Yii::info("[VideoTemplate:redisKey]:[$redisKey]");

        $return = Tools::getRedis(self::$redisDb, $redisKey);

        if (!empty($return) && isset($return['hit']) && $return['hit'] && Tools::isReturnSource(
            ) === false && $query->prep != 1) {
            \Yii::info("video template search data source from redis", __METHOD__);
            return $return;
        }


        $responseData['hit'] = 0;
        $responseData['ids'] = [];
        $responseData['score'] = [];

        try {
            $info = self::find()
                ->source(['id'])
                ->query($query->query())
                ->orderBy($query->sort)
                ->offset($query->offset)
                ->limit($query->pageSize)
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];

            if (isset($info['hits']) && $info['hits']) {
                $total = $info['total'] ?? 0;
                $responseData['hit'] = $total > 10000 ? 10000 : $total;
                foreach ($info['hits'] as $value) {
                    $responseData['ids'][] = $value['_id'] ?? 0;
                    $responseData['score'][$value['_id']] = $value['sort'][0] ?? [];
                }
            }
        } catch (Exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            throw new Exception($e->getMessage());
        }

        Tools::setRedis(self::$redisDb, $redisKey, $responseData, 86400);

        return $responseData;
    }
}
