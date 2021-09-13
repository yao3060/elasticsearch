<?php


namespace app\models;


use yii\db\ActiveRecord;

class TaskTemplateLink extends ActiveRecord
{
    public static function tableName()
    {
        return 'ips_task_template_link';
    }
}
