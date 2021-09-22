<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class DesignerRecommendAssetTagLink extends ActiveRecord {

    public static function tableName() {
        return 'ips_designer_recommend_asset_tag_link';
    }
}
