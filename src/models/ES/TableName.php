<?php

namespace app\models\ES;

use yii\db\ActiveRecord;

class TableName extends ActiveRecord
{
    public static function tableName()
    {
        return 'ips_es_table_name';
    }
}
