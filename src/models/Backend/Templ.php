<?php


namespace app\models\Backend;


use app\models\BackendActiveRecord;

class Templ extends BackendActiveRecord
{
    public static function tableName()
    {
        return 'ips_template';
    }
}
