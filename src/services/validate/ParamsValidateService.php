<?php

namespace app\services\validate;

use app\models\ParamsValidateModel;
use yii\base\Component;

/**
 * Class ParamsValidateService
 * @package app\services\validate
 * @method array getErrors(\string $attribute)
 * @method array getFirstErrors()
 * @method array getFirstError(\string $attribute)
 * @method array getErrorSummary(\boolean $showAllErrors)
 */
class ParamsValidateService extends Component
{
    /**
     * @var ParamsValidateModel 模型
     */
    private $model = null;

    public function init()
    {
        parent::init();

        $this->model = new ParamsValidateModel();
    }

    /**
     * @param  array  $data  数据项
     * @param  array  $rules  验证规则
     * @return bool
     */
    public function validate(&$data, $rules)
    {
        // 添加验证规则
        $this->model->setRules($rules);

        // 设置参数
        $this->model->load($data, '');

        // 进行验证
        $valid = $this->model->validate();

        // 覆盖值，使 default 验证器生效。
        $data = $this->model->attributes;

        return $valid;
    }

    /**
     * 获取验证通过的属性
     */
    public function getAttributes()
    {
        return $this->model->getAttributes();
    }

    /**
     * 获取第一条验证错误消息内容
     * @return mixed
     */
    public function getFirstErrorSummary()
    {
        $errors = $this->getErrorSummary(false);
        return current($errors);
    }

    public function __call($name, $params)
    {
        if ($this->model->hasMethod($name)) {
            return call_user_func_array([$this->model, $name], $params);
        } else {
            return parent::__call($name, $params);
        }
    }
}
