<?php

namespace app\models;

class Log extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%logs}}';
    }
}
