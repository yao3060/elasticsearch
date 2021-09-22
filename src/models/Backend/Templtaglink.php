<?php


namespace app\models\Backend;

use app\models\BackendActiveRecord;

class Templtaglink extends BackendActiveRecord
{
    public static function tableName()
    {
        return 'ips_template_tag_link';
    }
}
