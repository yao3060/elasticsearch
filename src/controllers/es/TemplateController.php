<?php

namespace app\controllers\es;

use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\Template;
use app\queries\ES\TemplateRecommendSearchQuery;
use app\queries\ES\TemplateSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class TemplateController extends BaseController
{
    /**
     * @api {get} /v1/templates Get Template
     * @apiName GetTemplate
     * @apiGroup Template
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [kid1] 一级版式
     * @apiParam (请求参数) {Number} [kid2] 二级版式
     * @apiParam (请求参数) {String} [sort_type] 排序  byyesday：昨日热门 ；bymonth：热门下载；bytime：最新上传
     * @apiParam (请求参数) {Number} [tag_id] 风格
     * @apiParam (请求参数) {Boolean} [is_zb] 是否可商用   >= 1 可商用
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {Number} [ratio] 版式  null：全部 1：横图；2：竖图；0：方图
     * @apiParam (请求参数) {String[]} [class_id] 分类
     * @apiParam (请求参数) {Number} [update] 1：强制回源
     * @apiParam (请求参数) {Number} [size] 模板大小（已下线）
     * @apiParam (请求参数) {Number} [fuzzy] 1：关键词（有交集）就会出现   但会降低性能  实测 3个词速度降低50%  8个词速度降低87.5%
     * @apiParam (请求参数) {string[]} [template_type] 模板类型 1.普通模板；2.GIF模板 3.ppt模板 4.视频模板 5.H5模板 & 长页H5
     * @apiParam (请求参数) {String[]} [color] 颜色搜索
     * @apiParam (请求参数) {Number} [use] 强制匹配类型(已下线）
     * @apiParam (请求参数) {Number} [width] 宽
     * @apiParam (请求参数) {Number} [height] 高
     * @apiParam (请求参数) {Number} [class_intersection_search] 0：class查询规则（包含）才能出现  1：class查询规则（有交集）就会出现
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {String} data.hit 命中数
     * @apiSuccess (应答字段) {String[]} data.ids 模板id集合
     * @apiSuccess (应答字段) {String[]} data.score 计算分数
     * @apiSuccess (应答字段) {Number} data.total 模板数量
     */
    public function actionSearch(Request $request)
    {
        try {
            $template = new Template();

            $validate = DynamicModel::validateData($request->getQueryParams(), $template->rules());

            if ($validate->hasErrors()) {
                return $this->response(new Response(
                    'validate params error',
                    'Validate Params Error',
                    $validate->errors,
                    422));
            }

            $validateAttributes = $validate->getAttributes();

            $search = $template->search(new TemplateSearchQuery(
                keyword: $validateAttributes['keyword'] ?? null,
                page: $validateAttributes['page'] ?? 1,
                kid1: $validateAttributes['kid1'] ?? 0,
                kid2: $validateAttributes['kid2'] ?? 0,
                sortType: $validateAttributes['sort_type'] ?? 'default',
                tagId: $validateAttributes['tag_id'] ?? 0,
                isZb: $validateAttributes['is_zb'] ?? 1,
                pageSize: $validateAttributes['page_size'] ?? 35,
                ratio: $validateAttributes['ratio'] ?? null,
                classId: $validateAttributes['class_id'] ?? 0,
                update: $validateAttributes['update'] ?? 0,
                size: $validateAttributes['size'] ?? 0,
                fuzzy: $validateAttributes['fuzzy'] ?? 0,
                templateTypes: $validateAttributes['template_type'] ?? [1, 2],
                use: $validateAttributes['use'] ?? 0,
                color: $validateAttributes['color'] ?? [],
                width: $validateAttributes['width'] ?? 0,
                height: $validateAttributes['height'] ?? 0,
                classIntersectionSearch: $validateAttributes['class_intersection_search'] ?? 0,
                elasticsearchColor: $validateAttributes['elasticsearch_color'] ?? ''
            ));

            $response = new Response('es_template_search', 'ESTemplate Search', $search);

        } catch (UnknownPropertyException $unknownException) {

            $response = new Response(
                StringHelper::snake($unknownException->getName()),
                StringHelper::replaceModelName($unknownException->getMessage()),
                [],
                422);

        } catch (\Throwable $throwable) {

            $response = new Response(
                'Internal Server Error',
                $throwable->getMessage(),
                YII_DEBUG ? explode("\n", $throwable->getTraceAsString()) : [],
                500);

        }

        return $this->response($response);
    }

    /**
     * @api {get} /v1/templates/recommends Get Recommend Template
     * @apiName GetRecommendTemplate
     * @apiGroup Template
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {Number} [ratio] 版式  null：全部 1：横图；2：竖图；0：方图
     * @apiParam (请求参数) {string[]} [template_type] 模板类型 1.普通模板；2.GIF模板 3.ppt模板 4.视频模板 5.H5模板 & 长页H5
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {String} data.hit 命中数
     * @apiSuccess (应答字段) {String[]} data.ids 模板id集合
     * @apiSuccess (应答字段) {String[]} data.score 计算分数
     */
    public function actionRecommendSearch(Request $request)
    {
        try {

            $template = new Template();

            $validate = DynamicModel::validateData($request->getQueryParams(), $template->recommendRules());

            if ($validate->hasErrors()) {
                return $this->response(new Response(
                    'validate_param_errors',
                    'Validate Param Errors',
                    $validate->errors,
                    422));
            }

            $attributes = $validate->getAttributes();

            $recommendSearch = $template->recommendSearch(new TemplateRecommendSearchQuery(
                keyword: $attributes['keyword'] ?? 0,
                page: $attributes['page'] ?? 1,
                pageSize: $attributes['page_size'] ?? 40,
                templateType: $attributes['template_type'] ?? null,
                ratio: $attributes['ratio'] ?? null
            ));

            $response = new Response(
                'es_template_commend_search',
                'ESTemplate Commend Search',
                $recommendSearch);

        } catch (UnknownPropertyException $unknownException) {

            $response = new Response(
                StringHelper::snake($unknownException->getName()),
                StringHelper::replaceModelName($unknownException->getMessage()),
                [],
                422);

        } catch (\Throwable $throwable) {

            $response = new Response(
                'Internal Server Error',
                $throwable->getMessage(),
                YII_DEBUG ? explode("\n", $throwable->getTraceAsString()) : [],
                500);

        }

        return $this->response($response);

    }
}
