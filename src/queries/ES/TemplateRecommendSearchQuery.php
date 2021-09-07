<?php


namespace app\queries\ES;


use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\ES\Template;

class TemplateRecommendSearchQuery implements QueryBuilderInterface
{
    /**
     * 搜索词
     * @var mixed|string
     */
    public $keywords;

    /**
     * 页码
     * @var int|mixed
     */
    public $page;

    /**
     * 每页数量
     * @var int|mixed
     */
    public $pageSize;

    /**
     * 模板类型
     * @var mixed|null
     */
    public $templateType;

    /**
     * 比例
     * @var mixed|null
     */
    public $ratio;

    /**
     * query build return
     * @var
     */
    private $query;

    public function __construct($params)
    {
        $this->keywords = $params['keywords'] ?? 0;
        $this->page = $params['page'] ?? 1;
        $this->pageSize = $params['page_size'] ?? 40;
        $this->templateType = $params['template_type'] ?? null;
        $this->ratio = $params['ratio'] ?? null;
    }

    /**
     * implode redis key
     * @return string
     */
    public function makeRedisKey()
    {
        $implodeKeys = [
            $this->keywords, $this->page, $this->pageSize,
            $this->templateType, $this->ratio
        ];

        return "ESTemplate:recommendSearch:" . implode('-', $implodeKeys);
    }

    public function query(): array
    {

        $key = $this->makeRedisKey();

        $return = Tools::getRedis(Template::$redis_db, $key);

//        if(empty($return) || Tools::isReturnSource()){
        if (empty($return)) {
            if ($this->keywords) {
                $this->query['bool']['must'][]['match']['title'] = $this->keywords;
            }
            if ($this->templateType != null) {
                $this->query['bool']['must'][]['match']['template_type'] = $this->templateType;
            }
            if ($this->ratio != null) {
                $this->query['bool']['must'][]['match']['ratio'] = $this->ratio;
            }

        }

        return [
            'key' => $key,
            'query' => $this->query
        ];
    }
}
