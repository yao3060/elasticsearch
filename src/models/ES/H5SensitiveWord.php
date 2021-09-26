<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

/**
 * @package app\models\ES
 * author  ysp
 */
class H5SensitiveWord extends BaseModel
{
    /**
     * @var int redis
     */
    private $redisDb = 8;

    public static function index()
    {
        return 'h5_ban_words';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return ["id", "word", "_word"];
    }

    /**
     * @param \app\queries\ES\H5SensitiveWordSearchQuery $query
     * @return array 2021-09-18
     */
    public function checkBanWord(QueryBuilderInterface $query)
    {
        if (empty($query->word)) {
            return false;
        }
        $is_ban_word['flag'] = false;
        try {
            $find = self::find()
                ->source(['word'])
                ->query($query->query())->limit(10000)
                ->createCommand()
                ->search()['hits'];
            if ($find['total'] <= 0) {
                return false;
            }

            $BanWord = '';
            foreach ($find['hits'] as $item) {
                $item['_source']['word'] = str_replace(" ", '', $item['_source']['word']);
                if (strstr($query->word, $item['_source']['word'])) {
                    $BanWord = $item['_source']['word'];
                    $is_ban_word['flag'] = true;
                    $is_ban_word['BanWord'] = $BanWord;
                }
            }
        } catch (\exception $e) {
        }
        $flag = $is_ban_word['flag'];

        return [
            'flag'=>$flag,
            'word'=>$BanWord
        ];
    }

}
