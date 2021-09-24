<?php

namespace app\models\Backend;

use app\models\BackendActiveRecord;

class AssetUseTop extends BackendActiveRecord
{
    public static function tableName()
    {
        return 'ips_asset_use_top';
    }

    /**
     * Get Latest Record By Column
     *
     * @param string $column
     * @param integer $value
     * @return array|null
     */
    public static function getLatestBy(string $column = 'kid_1', $value = 1): ?array
    {
        return self::find()
            ->where([$column => $value])
            ->orderBy('day DESC')
            ->asArray()
            ->one();
    }
}
