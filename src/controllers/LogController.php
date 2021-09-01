<?php

namespace app\controllers;

use app\models\Log;
use yii\rest\ActiveController;

class LogController extends ActiveController
{
    public $modelClass = Log::class;
}
