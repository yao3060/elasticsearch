<?php


namespace app\queries\ES;

use app\components\IpsAuthority;
use app\interfaces\ES\QueryBuilderInterface;

abstract class BaseTemplateSearchQuery implements QueryBuilderInterface
{
    private $query = [];

    public function sortByTime()
    {
        return 'created desc';
    }

    public function sortByHot()
    {
        return 'edit desc';
    }

    public function sortDefault()
    {
        # code...
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
            if ($this->kid1 == 1) {
                $this->query['bool']['must_not'][]['terms']['class_id'] = ['437', '760', '290', '810', '902'];
            }
        }

        return $this;
    }

    protected function queryTemplateTypes()
    {
        if (count($this->templateTypes) > 1 && in_array(5, $this->templateTypes)) {
            //有H5就添加长页H5(不改变key,除了单独搜索H5类型)
            $this->templateTypes[] = 7;
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
     * @param array $hex_colors   十六进制颜色值
     * @param array $weights      颜色值对应的权重值 (0, 1]
     * @param int $e              颜色搜索范围
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
                 58,110,85,0.7,
                 214,28,59,0.1]
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
