<?php


namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;

class SensitiveWord extends BaseModel
{
    /**
     * Set (update) mappings for this model
     */
    public static function updateMapping()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->setMapping(static::index(), static::type(), static::mapping());
    }

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
     * Create this model's index
     */
    public static function createIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->createIndex(
            static::index(),
            [
                'settings' => ["number_of_shards" => 1, "number_of_replicas" => 1],//5个分片0个复制
                'mappings' => static::mapping(),
            ]
        );
    }

    public static function validateRules()
    {
        return [
            [['keyword'], 'required'],
            ['keyword', 'string']
        ];
    }

    public function attributes()
    {
        return ["id", "word", "_word"];
    }

    /**
     * @param  \app\queries\ES\SensitiveWordSearchQuery  $query
     * @return array
     * @throws Exception
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();

        \Yii::info("[SensitiveWord:redisKey]:[$redisKey]", __METHOD__);

        $validateSensitiveWord = Tools::getRedis(6, $redisKey);

        if (!empty($validateSensitiveWord) && isset($validateSensitiveWord['hit']) && $validateSensitiveWord['hit']
            && Tools::isReturnSource() === false) {
            \Yii::info("sensitive word search data source from redis", __METHOD__);
            return $validateSensitiveWord;
        }

        $validateSensitiveWord['flag'] = false;

        try {
            $find = self::find()
                ->source(['word'])
                ->query($query->query())
                ->createCommand()
                ->search()['hits'];
            if (isset($find['total']) && $find['total'] <= 0) {
                Tools::setRedis(6, $query->getRedisKey(), $validateSensitiveWord, 86400 * 7);
                $validateSensitiveWord['flag'] = false;
            }
            if (isset($find['hits']) && $find['hits']) {
                foreach ($find['hits'] as &$item) {
                    $item['_source']['word'] = str_replace(" ", '', $item['_source']['word']);
                    if (strstr($query->keyword, $item['_source']['word'])) {
                        $validateSensitiveWord['flag'] = true;
                    }
                }
            }
            Tools::setRedis(6, $query->getRedisKey(), $validateSensitiveWord, 86400 * 7);
        } catch (Exception $exception) {
            \Yii::error("SensitiveWord Model Error: " . $exception->getMessage(), __METHOD__);
        }

        return $validateSensitiveWord;
    }
}
