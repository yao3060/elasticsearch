<?php

namespace app\models;

use Yii;
use yii\helpers\Json;

class Assettaglink extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'ips_asset_tag_link';
    }

    public function attributeLabels() {
        return [
            'asset_id' => '素材ID',
            'tag_id' => '标签ID',
        ];
    }

    public function getById($id, $tid) {
        return self::findOne(['asset_id' => $id, 'tag_id' => $tid]);
    }

    //根据指定的assetID，来寻找是否有属于text艺术字类型的
    //艺术字的tagid是23
    //初期版本需要根据这个规则来区分艺术字和元素
    //$assetIds是数组
    public function getTextAssetIds($assetIds) {
        $query = new \yii\db\Query();
        $list = $query->where(['in', 'asset_id', $assetIds])->from('ips_asset_tag_link')->limit(100)->all();
        return array_reduce($list, function($v1, $v2) {
            if ($v2["tag_id"] == 23) {
                $v1[] = $v2["asset_id"];
            }
            return $v1;
        }, []);
    }

    public function deleteTags($id) {
        if ($id && $id > 0) {
            \Yii::$app->db->createCommand()
                ->delete(self::tableName(), ['asset_id' => $id])
                ->execute();
        }
    }

    //这里第二个参数tags可以是数组，也可以是空格分隔的id字符串
    //方法会自动区分并且转换
    //也就是说array(1,2,3)和"1 2 3"都可以
    public function addTags($id, $tags) {
        if (!$id && $id <= 0) {
            return;
        }

        if (is_array($tags)) {
            $tagArray = $tags;
        } else {
            $tagArray = explode(' ', $tags);
        }
        //先删除所有的asset_id为id的link
        $connection = \Yii::$app->db;
        $model = $connection->createCommand('DELETE FROM ips_asset_tag_link WHERE asset_id=:assetid');
        $model->bindParam(':assetid', $id);
        $model->execute();
        foreach ($tagArray as $t) {
            if (!empty($t)) {
                //注意下面这种防止重复记录的方式是先利用yii自己的find查找是否存在关联
                //如果不存在就创建model，新model的save方法会insert记录
                //如果存在的话就返回已有的model，这样再用save方法就不会重复insert，并且还自带update的功能
                //但mysql默认的ON DUPLICATE其实虽然只是一个语句
                //但实际查找机制也有可能是先查找是否存在再进行操作
                //所以不一定会有性能问题
                //这里可以稍后研究一下
                //首先查找是否存在关联
                $model = self::find()->where(array("asset_id" => $id, "tag_id" => $t))->one();
                //如果不存在就创建新关联
                if (!$model) $model = new self();
                $model->asset_id = intVal($id);
                $model->tag_id = intval($t);
                $model->save();
            }
        }
    }

    public static function getTags($id) {
        $condition = ["asset_id" => $id];
        $linkList = self::find()->where($condition)->all();
        $tags = [];
        return array_reduce($linkList, function($tags, $item) {
            $tags[] = $item->tag_id;
            return $tags;
        });
    }

    /**
     * 根据asset_id获取标签
     *
     * @param $aid
     * @return array|bool|mixed|\yii\db\ActiveRecord[]
     */
    public static function getTagsByAid($aid) {
        $cacheKey = "asset:tags:" . $aid;
        $list = Redis::get($cacheKey);
        if ($list === null) {
            $list = static::find()
                ->alias('l')
                ->select('t.id, t.tagname')
                ->leftJoin(Assettag::tableName() . ' t', 't.id = l.tag_id')
                ->where(['l.asset_id' => $aid, 't.remove' => 0])
                ->asArray()
                ->all();
            Redis::set($cacheKey, $list, 3600 * 24);
        }
        return $list;
    }
}
