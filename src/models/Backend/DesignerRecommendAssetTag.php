<?php

namespace app\models\Backend;

use app\components\Tools;
use app\models\BackendActiveRecord;

class DesignerRecommendAssetTag extends BackendActiveRecord
{

    public static function tableName()
    {
        return 'ips_designer_recommend_asset_tag';
    }

    public function getAsset()
    {
        return $this->hasMany(DesignerRecommendAssetTagLink::class, ['tag_id' => 'id']);
    }

    //    非vip图片
    public function getAssetv2()
    {
        return $this->hasMany(DesignerRecommendAssetTagLink::class, ['tag_id' => 'id'])->alias('ra')->leftJoin('ips_asset_is_not_vip av', 'av.asset_id = ra.asset_id')->where(['>', 'av.asset_id', 0]);
    }

    public static function getRecommendAsset($type)
    {
        $redis_key = "designer_recommend_left_asset_tag_info_v1:" . $type;
        $list = Tools::getRedis(9, $redis_key);
        if (!$list || Tools::isReturnSource()) {
            $list = self::find()
                ->where(['deleted' => 0])
                ->andWhere(['type' => $type])
                ->orderBy('sort desc')
                ->with('asset')
                ->asArray()->all();
            Tools::setRedis(9, $redis_key, $list, 86400);
        }
        return $list;
    }

    public static function getRecommendAssetNew($type, $vip_pic)
    {
        $redis_key = "designer_recommend_left_asset_tag_info_v1:" . $type . "_" . $vip_pic;
        //        $list = Tools::getRedis(9,$redis_key);
        if (!$list || Tools::isReturnSource()) {
            $list = self::find()->alias('rat')
                ->where(['rat.deleted' => 0])
                ->andWhere(['rat.type' => $type])
                ->orderBy('rat.sort desc')
                ->with('assetv2')
                ->asArray()->all();
            //            Tools::setRedis(9,$redis_key,$list,86400);
        }
        return $list;
    }
}
