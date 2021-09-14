<?php


namespace app\queries\ES;

use app\components\IpsAuthority;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\ES\Template;

abstract class BaseTemplateSearchQuery implements QueryBuilderInterface
{
    protected $query = [];

    public function sortByTime()
    {
        return 'created desc';
    }

    public function sortByHot()
    {
        return 'edit desc';
    }

    public function sortDefault($keyword, $class_id = [], $index_name = null, string $esTableName)
    {
        $index_name = !empty($index_name) ? $index_name : $esTableName;
        //        $source = "doc['pr'].value-doc['man_pr'].value+doc['man_pr_add'].value";
        if ($class_id && is_array($class_id) == false) {
            $class_id = explode('_', $class_id);
        }
        $source = "doc['pr'].value+(int)(_score*10)";
        if (strstr($keyword, 'h5') || strstr($keyword, 'H5')) {
            $source .= "+10000-((int)(doc['template_type'].value-5)*(int)(doc['template_type'].value-5)*400)";
        }
        if ($keyword) {
            //关键词人工pr
            $mapping = Template::getMapping();
            $hot_keyword = [];
            if (isset($mapping[$index_name]['mappings']['list']['properties']['hot_keyword']['properties']) && $mapping[$index_name]['mappings']['list']['properties']['hot_keyword']['properties']) {
                foreach ($mapping[$index_name]['mappings']['list']['properties']['hot_keyword']['properties'] as $kk => $property) {
                    if ($property['type'] == 'long') {
                        $hot_keyword[] = (string)$kk;
                    }
                }
                if (in_array((string)$keyword, $hot_keyword, true)) {
                    $source .= "+doc['hot_keyword.{$keyword}'].value";
                }
            }
            // 根据展示点击率调整pr
            //            $optimize_keyword = array_keys($mapping[$index_name']['mappings']['list']['properties']['keyword_show_edit']['properties']);
            //            $optimize_keyword = explode('!!!', implode('!!!', $optimize_keyword));//强制转换为string类型
            //            if (in_array((string)$keyword, $optimize_keyword)) {
            //                $source .= "+doc['keyword_show_edit.{$keyword}'].value";
            //            }

        } elseif ($class_id && count($class_id) >= 1) {
            //标签的人工pr
            $choose_class_id = 0;
            foreach ($class_id as $v) {
                if ($v > 0 || $v == -1) {
                    $choose_class_id = $v;
                }
            }
            if ($choose_class_id > 0 || $choose_class_id == -1) {
                $mapping = Template::getMapping();
                if (isset($mapping[$index_name]['mappings']['list']['properties']['class_sort']['properties']) && $mapping[$index_name]['mappings']['list']['properties']['class_sort']['properties']) {
                    $class_sort = array_keys($mapping[$index_name]['mappings']['list']['properties']['class_sort']['properties']);
                    $class_sort = explode('!!!', implode('!!!', $class_sort));//强制转换为string类型
                    if (in_array((string)$choose_class_id, $class_sort)) {
                        $source .= "+doc['class_sort.{$choose_class_id}'].value";
                    }
                }
            }
        }
        $sort['_script'] = [
            'type' => 'number',
            'script' => [
                "lang" => "painless",
                "source" => $source
            ],
            'order' => 'desc'
        ];
        return $sort;
    }

    protected function queryKeyword()
    {
        if (!empty($this->keyword)) {
            $operator = isset($this->fuzzy) && $this->fuzzy ? 'or' : 'and';
            $fields = ["title^16", "description^2", "hide_description^2", "brief^2", "info^1"];
            if ($operator == 'or') {
                $this->keyword = str_replace(['图片'], '', $this->keyword);
                $fields = ["title^16", "description^2", "hide_description^2", "info^1"];
            }
            if (in_array($this->keyword, ['LOGO', 'logo'])) {
                $fields = ["title^16", "description^2", "hide_description^2", "info^1"];
            }
            $this->query['bool']['must'][]['multi_match'] = [
                'query' => $this->keyword,
                'fields' => $fields,
                'type' => 'most_fields',
                "operator" => $operator
            ];
        }

        return $this;
    }

    protected function queryIosAlbumUser()
    {
        if (IpsAuthority::check(IOS_ALBUM_USER)) {
            $this->query['bool']['must_not'][]['match']['hide_in_ios'] = 1;
        }
        return $this;
    }

    protected function queryKid1()
    {
        if ($this->kid1 && $this->kid1 !== 1) {
            $this->query['bool']['must'][]['match']['kid_1'] = $this->kid1;
        }
        return $this;
    }

    protected function queryKid2()
    {
        if ($this->kid2) {
            if ($this->kid2 == 21) {
                //公众号配图特殊化处理
                $this->query['bool']['must'][]['terms']['kid_2'] = [20, 21, 22];
            } else {
                $this->query['bool']['must'][]['match']['kid_2'] = $this->kid2;
            }
        }
        return $this;
    }

    protected function queryRatio()
    {
        if ($this->ratio != null) {
            $this->query['bool']['must'][]['match']['ratio'] = $this->ratio;
        }
        return $this;
    }

    protected function queryTagIds()
    {
        if ($this->tagId) {
            $tagIds = explode('_', $this->tagId);
            foreach ($tagIds as &$item) {
                $item = (int)$item;
            }
            $this->query['bool']['must'][]['terms']['tag_id'] = $tagIds;
        }
        return $this;
    }

    protected function queryIsZb()
    {
        if ($this->isZb >= 1) {
            //可商用
            $this->query['bool']['filter'][]['range']['is_zb']['gte'] = 1;
        } elseif ($this->isZb === '_1') {
            //不可商用
            $this->query['bool']['must'][]['match']['is_zb'] = 0;
        }

        return $this;
    }

    protected function queryClassIds()
    {
        if ($this->classId) {
            $classId = explode('_', $this->classId);
            if (in_array(760, $classId)) {
                //视频模板要特殊处理
                $this->templateType = 4;
            }
            foreach ($classId as $key) {
                if ($key > 0) {
                    $this->query['bool']['must'][]['terms']['class_id'] = [$key];
                }
            }
        } else {
            if (isset($this->kid1) && $this->kid1 == 1) {
                $this->query['bool']['must_not'][]['terms']['class_id'] = ['437', '760', '290', '810', '902'];
            }
        }

        return $this;
    }

    protected function queryTemplateTypes()
    {
        // 只有数组才 count
        if (is_array($this->templateTypes)) {
            if (count($this->templateTypes) > 1 && in_array(5, $this->templateTypes)) {
                //有H5就添加长页H5(不改变key,除了单独搜索H5类型)
                $this->templateTypes[] = 7;
            }
        }

        if (is_array($this->templateTypes) && !empty($this->templateTypes)) {
            $this->query['bool']['must'][]['terms']['template_type'] = $this->templateTypes;
        } elseif (is_numeric($this->templateTypes) && $this->templateTypes > 0) {
            $this->query['bool']['must'][]['match']['template_type'] = $this->templateTypes;
        }
        return $this;
    }


    protected function queryTemplateAttr()
    {
        if ($this->templateAttr) {
            $this->query['bool']['must'][]['term']['templ_attr'] = $this->templateAttr;
        }
        return $this;
    }

    protected function querySettlementLevel()
    {
        if ($this->settlementLevel) {
            $this->query['bool']['must'][]['match']['settlement_level'] = $this->settlementLevel;
        }
        return $this;
    }

    /**
     * query width
     */
    public function queryWidth()
    {
        if (!empty($this->width)) {
            $this->query['bool']['filter'][]['match']['width'] = $this->width;
        }

        return $this;
    }

    /**
     * query height
     */
    public function queryHeight()
    {
        if (!empty($this->width)) {
            $this->query['bool']['filter'][]['match']['width'] = $this->width;
        }

        return $this;
    }

    /**
     * @param array $hex_colors 十六进制颜色值
     * @param array $weights 颜色值对应的权重值 (0, 1]
     * @param int $e 颜色搜索范围
     * @return array
     */
    public function formatColor($hex_colors, $weights = [100], $e = 50)
    {
        if (count($hex_colors) == 1) {
            $e = 80;
        }
        $colors = [];
        foreach ($hex_colors as $key => $value) {
            $current_w = $weights[$key] / 100 ?? 1;
            $r = hexdec(substr($value, 0, 2));
            $g = hexdec(substr($value, 2, 2));
            $b = hexdec(substr($value, 4, 2));
            array_push($colors, $r, $g, $b, $current_w);
        }

        $colorRange = [];
        $color = $this->getColorFeature($colors);
        foreach ($color as $c) {
            $min = $c - $e >= 0 ? $c - $e : 0;
            $max = $c + $e <= 255 ? $c + $e : 255;
            array_push($colorRange, ['from' => $min, 'to' => $max]);
        }
        $colorParams = $this->getColorField($colors); //color->params->center

        return [$colorRange, $colorParams];
    }

    /**
     * 函数名称：获取颜色特征值 缩小搜索区域
     * 输入形如 [128,186,200,0.2,
     * 58,110,85,0.7,
     * 214,28,59,0.1]
     *  输出形如 [87,117,105]
     */
    private function getColorFeature($colors)
    {
        $r = $g = $b = $w = 0;
        for ($i = 0; $i < count($colors) / 4; ++$i) {
            $offset = 4 * $i;
            $current_w = $colors[$offset + 3];
            $r += $colors[$offset] * $current_w;
            $g += $colors[$offset + 1] * $current_w;
            $b += $colors[$offset + 2] * $current_w;
            $w += $current_w;
        }
        if ($w == 0) {
            return [0, 0, 0];
        } else {
            return [0 => $r / $w, 1 => $g / $w, 2 => $b / $w];
        }
    }

    /**
     * 进行颜色编码
     * 入参 必选 形如 [128,186,200,0.2, 58,110,85,0.7,214,28,59,0.1]
     */
    public static function getColorField($colors)
    {
        $str = "";
        for ($i = 0; $i < count($colors) / 4; ++$i) {
            $offset = 4 * $i;
            $current_w = $colors[$offset + 3];
            $r = sprintf("%02x", $colors[$offset] & 0xff);
            $g = sprintf("%02x", $colors[$offset + 1] & 0xff);
            $b = sprintf("%02x", $colors[$offset + 2] & 0xff);
            $w = sprintf("%02x", (int)($current_w * 100));
            $str = $str . $r . $g . $b . $w . "_";
        }

        return substr($str, 0, strlen($str) - 1);
    }
}
