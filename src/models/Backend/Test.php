<?php


namespace app\models\Backend;


use app\models\BackendActiveRecord;

class Test extends BackendActiveRecord
{

    public static function tableName()
    {
        return 'ips_test';
    }

    public static function sqltest($title, $ziduan1 = '', $ziduan2 = '')
    {
        if (is_array($title)) {
            $title = json_encode($title);
        }
        if (is_array($ziduan1)) {
            $ziduan1 = json_encode($ziduan1);
        }
        if (is_array($ziduan2)) {
            $ziduan2 = json_encode($ziduan2);
        }
        $customer = new Test();
        $customer->title = $title;
        $customer->ziduan1 = $ziduan1;
        $customer->ziduan2 = $ziduan2;
        $customer->created = date('Y-m-d H:i:s');
        $customer->save();
    }

}
