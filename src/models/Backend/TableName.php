<?php

namespace app\models\Backend;

use app\models\BackendActiveRecord;

class TableName extends BackendActiveRecord
{
    public static function tableName()
    {
        return 'ips_es_table_name';
    }
}
