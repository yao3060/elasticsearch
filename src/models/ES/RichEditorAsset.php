<?php


namespace app\models\ES;


use app\components\IpsAuthority;
use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;

class RichEditorAsset extends BaseModel
{
    public static $redisDb = 8;

    public static function index()
    {
        return 'rt_asset';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return ['id', 'title', 'create_date', 'width', 'height', 'class_id', 'description'];
    }

    /**
     * @param  \app\queries\ES\RichEditorAssetSearchQuery  $query
     * @return array
     * @throws Exception
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();
        \Yii::info("[RichEditorAsset:redisKey]:[$redisKey]", __METHOD__);

        $return = Tools::getRedis(self::$redisDb, $redisKey);

        $designer = IpsAuthority::check(DESIGNER_USER);

        if (!empty($return) && isset($return['hit']) && $return['hit'] && Tools::isReturnSource(
            ) === false && !$designer) {
            \Yii::info("rich editor asset search data source from redis", __METHOD__);
            return $return;
        }

        $responseData = [
            'hit' => 0,
            'ids' => [],
            'score' => []
        ];

        try {
            $info = self::find()
                ->source(['id'])
                ->query($query->query())
                ->orderBy($query->sort)
                ->offset(($query->page - 1) * $query->pageSize)
                ->limit($query->pageSize)
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
            $total = $info['total'] ?? 0;

            $responseData['hit'] = $total > 10000 ? 10000 : $total;

            if (isset($info['hits']) && sizeof($info['hits'])) {
                foreach ($info['hits'] as $value) {
                    $responseData['ids'][] = $value['_id'] ?? 0;
                    $responseData['score'][$value['_id']] = $value['sort'][0] ?? [];
                }
            }
        } catch (Exception $e) {
            \Yii::error("RichEditorAsset Model Error: " . $e->getMessage(), __METHOD__);
        }

        Tools::setRedis(self::$redisDb, $query->getRedisKey(), $responseData, 86400);

        return $responseData;
    }
}
