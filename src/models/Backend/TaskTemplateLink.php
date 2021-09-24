<?php


namespace app\models\Backend;


use app\models\BackendActiveRecord;

class TaskTemplateLink extends BackendActiveRecord
{
    public static function tableName()
    {
        return 'ips_task_template_link';
    }
}
