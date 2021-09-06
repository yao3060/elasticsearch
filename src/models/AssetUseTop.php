<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\components\Tools;

class AssetUseTop extends ActiveRecord {
    // public $imageFile;
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'ips_asset_use_top';
    }

    public static function getCount($kid_1) {
        $total = Asset::find()->where('audit_through = 4 AND deleted = 0 AND kid_1 =1')->count();
        $top1 = ceil($total / 100 * 1);
        $top2 = ceil($total / 100 * 2);
        $top5 = ceil($total / 100 * 5);
        $use_count = OnlineTemplAssetLink::find()
            ->select('asset_id,count( * ) count')
            ->alias('b')
            ->leftJoin('`ips_asset` `a` ', ' a.id = b.asset_id ')
            ->where('a.audit_through = 4 AND a.deleted = 0 AND kid_1 = ' . $kid_1)
            ->groupBy('asset_id')
            ->orderBy('count DESC ')
            ->asArray()
            ->limit($top5)
            ->all();
        $top1_count = $use_count[$top1 - 1]['count'];
        $top2_count = $use_count[$top2 - 1]['count'];
        $top5_count = $use_count[$top5 - 1]['count'];
        $add = new self();
        $add->top1 = $top1;
        $add->top2 = $top2;
        $add->top5 = $top5;
        $add->top1_count = $top1_count;
        $add->top2_count = $top2_count;
        $add->top5_count = $top5_count;
        $add->day = date('Y-m-d');
        $add->kid_1 = $kid_1;
        $add->save();
    }

    public static function getLastInfo($kid_1) {
        $find = self::find()
            ->where('kid_1 = ' . $kid_1)
            ->orderBy('day DESC')
            ->limit(1)
            ->asArray()
            ->one();
        return $find;
    }

}
