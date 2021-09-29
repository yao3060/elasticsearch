<?php

namespace app\models\Backend;

use app\models\BackendActiveRecord;

class DesignerRecommendAssetTagLink extends BackendActiveRecord
{
    public static function tableName()
    {
        return 'ips_designer_recommend_asset_tag_link';
    }
}
