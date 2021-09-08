<?php


namespace app\models;


use app\components\Tools;
use yii\db\ActiveRecord;
use Yii;

class Templtaglink extends ActiveRecord
{
    public static function tableName()
    {
        return 'ips_template_tag_link';
    }
}
