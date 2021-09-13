<?php


namespace app\models;


use yii\db\ActiveRecord;

class Templ extends ActiveRecord
{
    public static function tableName()
    {
        return 'ips_template';
    }
}
