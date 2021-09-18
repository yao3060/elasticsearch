<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class BackendActiveRecord extends ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('backend_db');
    }
}
