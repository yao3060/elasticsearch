<?php

namespace app\controllers\es;

use app\controllers\BaseController;
use yii\base\Request;

class DesignerTemplateController extends BaseController
{

  public function actionIndex(Request $request)
  {
    $items = (new DesignerTemplate)->search(new DesignerTemplateSearchQuery());
    return $items;
  }
}
