<?php


namespace app\models\ES;


use app\components\IpsAuthority;
use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;

class RichEditorAsset extends BaseModel
{
    public static $redis_db = 8;

    public static function index()
    {
        return 'rt_asset';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes() {
        return ['id', 'title', 'create_date', 'width', 'height', 'class_id','description'];
    }

    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::$redis_db, $query->getRedisKey());

        $designer = IpsAuthority::check(DESIGNER_USER);
        if (!$return || Tools::isReturnSource() || $designer) {
            unset($return);
        }

        $return['hit'] = 0;
        $return['ids'] = [];
        $return['score'] = [];

        try {
            $info = self::find()
                ->source(['id'])
                ->query($query->query())
                ->orderBy($query->sort)
                ->offset(($query->page - 1) * $query->pageSize)
                ->limit($query->pageSize)
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
        } catch (\exception $e) {
            throw new Exception($e->getMessage());
        }

        $total = $info['total'] ?? 0;

        $return['hit'] = $total > 10000 ? 10000 : $total;

        if (isset($info['hits']) && sizeof($info['hits'])) {
            foreach ($info['hits'] as $value) {
                $return['ids'][] = $value['_id'];
                $return['score'][$value['_id']] = $value['sort'][0];
            }
        }

//        Tools::setRedis(self::$redis_db, $query->getRedisKey(), $return, 86400);

        return $return;
    }
}
