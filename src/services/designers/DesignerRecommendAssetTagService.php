<?php

namespace app\services\designers;

use app\components\Tools;
use app\models\Backend\DesignerRecommendAssetTag;

/**
 * 设计师服务
 * Class DesignerRecommendAssetTagService
 * @package app\service\designers
 */
class DesignerRecommendAssetTagService
{
    /**
     * 获取推荐的设计师编辑器左侧素材
     * @param $type
     * @return array
     */
    public static function getRecommendAssetTags($type): array
    {
        $redisKey = "designer_recommend_left_asset_tags_v1:" . $type;

        $list = Tools::getRedis(9, $redisKey);
        if (!$list) {
            $list = DesignerRecommendAssetTag::find()
                ->where(['deleted' => 0])
                ->andWhere(['type' => $type])
                ->orderBy('sort desc')
                ->select(['id', 'name', 'kw'])
                ->asArray()->all();

            Tools::setRedis(9, $redisKey, $list, 86400);
        }

        return $list;
    }

    public static function getRecommendAssetKws($type)
    {
        $assetTags = self::getRecommendAssetTags($type);

        return array_column($assetTags, 'kw');
    }
}
