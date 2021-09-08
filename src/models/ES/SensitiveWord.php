<?php


namespace app\models\ES;

use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;

class SensitiveWord extends BaseModel
{
    public static function index()
    {
        return 'ban_words';
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
                    'id' => ['type' => 'integer', 'include_in_all' => false],
                    'word' => ['type' => 'text', 'analyzer' => "ik_max_word", 'include_in_all' => true],
                    '_word' => ['type' => 'keyword', 'include_in_all' => false],
                ]
            ],
        ];
    }

    /**
     * Set (update) mappings for this model
     */
    public static function updateMapping()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->setMapping(static::index(), static::type(), static::mapping());
    }

    public static function getMapping()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        return $command->getMapping(static::index(), static::type(), static::mapping());
    }

    /**
     * Create this model's index
     */
    public static function createIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->createIndex(static::index(), [
            'settings' => ["number_of_shards" => 1, "number_of_replicas" => 1],//5个分片0个复制
            'mappings' => static::mapping(),
        ]);
    }

    public static function queryWord($word)
    {
        $query['bool']['must'][]['match']['word'] = [
            'query' => $word,
            "operator" => "or"
        ];
        return $query;
    }

    public function attributes()
    {
        return ["id", "word", "_word"];
    }

    public function search(QueryBuilderInterface $query): array
    {
        $searchQuery = self::queryWord($query->keyword);
        $returnQuery = $query->query();

        try {
            $find = self::find()
                ->source(['word'])
                ->query($searchQuery)
                ->createCommand()
                ->search()['hits'];
            if ($find['total'] <= 0) {
//                Tools::setRedis(6, $query->getRedisKey(), $query->query(), 86400 * 7);
                $returnQuery['flag'] = false;
            }
            foreach ($find['hits'] as &$item) {
                $item['_source']['word'] = str_replace(" ", '', $item['_source']['word']);
                if (strstr($query->keyword, $item['_source']['word'])) {
                    $returnQuery['flag'] = true;
                }
            }
//            Tools::setRedis(6, $query->getRedisKey(), $returnQuery, 86400 * 7);
        } catch (\exception $e) {
            throw new Exception($e->getMessage());
        }

        return $returnQuery;
    }

    public static function validateRules()
    {
        return [
            [['keyword'], 'required'],
            ['keyword', 'string']
        ];
    }
}
