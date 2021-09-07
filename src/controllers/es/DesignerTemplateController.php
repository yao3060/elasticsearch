<?php

namespace app\controllers\es;

use app\controllers\BaseController;
use app\models\ES\DesignerTemplate;
use app\queries\ES\DesignerTemplateSearchQuery;
use yii\base\Request;

class DesignerTemplateController extends BaseController
{

    public function actionIndex(Request $request)
    {
        $items = (new DesignerTemplate)->search(
            new DesignerTemplateSearchQuery(
                keyword: $request->get('keyword', ''),
                page: $request->get('page', 1),
                pageSize: $request->get('page_size', 40),
                kid1: $request->get('kid1', 0),
                kid2: $request->get('kid2', 0),
                templateInfo: ['picId' => 1, 'templ_attr' => 2, 'type' => 'a'],
                ratio: null,
                sortType: 'default'
            )
        );
        return $items;
    }
}
