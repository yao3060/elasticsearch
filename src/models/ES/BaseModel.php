<?php

namespace app\models\ES;

use app\interfaces\ES\ModelInterface;
use yii\elasticsearch\ActiveRecord;

abstract class BaseModel extends ActiveRecord implements ModelInterface
{
}
