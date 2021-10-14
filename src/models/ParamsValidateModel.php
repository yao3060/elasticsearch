<?php


namespace app\models;


use yii\db\ActiveRecord;

class ParamsValidateModel extends ActiveRecord
{
    /**
     * @var array 验证规则
     */
    private $_rules = [];

    /**
     * @var array 虚拟属性
     */
    private $_visionAttributes = [];

    /**
     * 设置验证规则
     */
    public function setRules($rules)
    {
        $this->_rules = $rules;

        foreach ($rules as $item) {
            $this->_visionAttributes = array_unique(array_merge($this->_visionAttributes, (array)$item[0]));
        }
    }

    /**
     * 重写获取验证规则
     */
    public function rules()
    {
        return $this->_rules;
    }

    /**
     * 设置可用属性列表
     */
    public function attributes()
    {
        return $this->_visionAttributes;
    }
}
