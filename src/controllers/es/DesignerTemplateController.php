<?php

namespace app\controllers\es;

use app\components\Response;
use app\controllers\BaseController;
use app\models\ES\DesignerTemplate;
use app\queries\ES\DesignerTemplateSearchQuery;
use yii\web\Request;

class DesignerTemplateController extends BaseController
{

    /**
     * @api {get} /v1/designer-templates Get Designer Template
     * @apiName GetDesignerTemplate
     * @apiGroup DesignerTemplate
     * @apiDescription 设计师模板，二次设计。（原 ips_backend 项目模型：ESTemplateSecond）
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
     * @apiParam (请求参数) {Object[]} [templ_info] 模板属性（精品 or 非精品，只在设计师使用）
     * @apiParam (请求参数) {String[]} [color] 颜色搜索
     * @apiParam (请求参数) {Number} [use] 强制匹配类型(已下线）
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {String} data.hit 命中数
     * @apiSuccess (应答字段) {String[]} data.ids 模板id集合
     * @apiSuccess (应答字段) {String[]} data.score 计算分数
     * @apiSuccess (应答字段) {Number} data.total 模板数量
     *
     * @apiSuccessExample {json} 应答事例
     *  {
     *     "code": "design_template_index",
     *     "message": "DesignTemplateIndex",
     *     "data": {
     *          "hit": 10000,
     *          "ids": [
     *               "3120406",
     *               "3352082",
     *               "3350802",
     *               "3376118",
     *               "3313747"
     *         ],
     *         "score": {
     *               "3120406": 5889,
     *               "3352082": 5865,
     *              "3350802": 5774,
     *              "3376118": 5668,
     *              "3313747": 5647
     *         },
     *         "total": 53726
     *      }
     *  }
     */
    public function actionIndex(Request $request)
    {
        try {

            $items = (new DesignerTemplate)->search(
                new DesignerTemplateSearchQuery(
                    keyword: $request->get('keyword', 0),
                    page: $request->get('page', 1),
                    kid1: $request->get('kid1', 0),
                    kid2: $request->get('kid2', 0),
                    sortType: $request->get('sort_type', 'default'),
                    tagId: $request->get('tag_id', 0),
                    isZb: $request->get('is_zb', 0),
                    pageSize: $request->get('page_size', 100),
                    ratio: $request->get('ratio', null),
                    classId: $request->get('class_id', 0),
                    update: $request->get('update', 0),
                    size: $request->get('size', 0),
                    fuzzy: $request->get('fuzzy', 0),
                    templateTypes: $request->get('template_type', [1, 2]),
                    templateInfo: $request->get('templ_info', []),
                    color: $request->get('color', []),
                    use: $request->get('use', 0)
                )
            );

            $response = new Response('design_template_index', 'DesignTemplateIndex', $items);

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
