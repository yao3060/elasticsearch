<?php

namespace app\components;

use Yii;

/**
 * 工具类
 *
 * @property integer
 */
class Tools
{
    const REDIS_EXPIRE = 86400;
    /**
     * 是否回源
     * @param int $prep
     * @return bool
     */
    public static function isReturnSource($prep = 0)
    {
        // 12336807 龙雨洁
        $uids = [41, 2626047, 1582045, 4867228, 3837014, 2936030, 9667287];
        //        2626047 朱天会
        //        1582045 殷龙龙
        //        4867228 何丽
        //        3837014 丁胜男
        //        9667287 储召琴
        //true 是控制台程序运行
        if (Yii::$app->id == 'basic-console') {
            $uid = 0;
        } else {
            $uid = Yii::$app->user->id;
        }
        if (($prep == 1 || (isset($_GET['prep']) && $_GET['prep'] == 1)) &&
            in_array($uid, $uids)
        ) {
            return true;
        }
        return false;
    }

    public static function setRedis($db = 2, $key, $value, $time = 86400)
    {
        if (!is_prod()) {
            return;
        }

        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }
        $redis = 'redis' . $db;
        return Yii::$app->$redis->set(
            $key,
            $value,
            'EX',
            $time > 0 ? $time : self::REDIS_EXPIRE
        );
    }

    public static function getRedis($db = 2, $key)
    {
        if (!is_prod()) {
            return null;
        }

        $redis = 'redis' . $db;
        $info = Yii::$app->$redis->get($key);
        if (!$info) {
            return false;
        }
        $return = maybe_unserialize($info);
        if ($return || $return === []) {
            return $return;
        }

        if (is_null(json_decode($info))) {
            return $info;
        } else {
            return json_decode($info, 1);
        }
    }

    public static function delRedis($db = 2, $key)
    {
        if (!is_prod()) {
            return null;
        }

        $redis = 'redis' . $db;
        Yii::$app->$redis->del($key);
    }
}
