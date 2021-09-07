<?php

namespace app\components;

use app\models\Authority;
use app\models\User;

class IpsAuthority
{
    public static function check($authorityId, $type = '')
    {
        return self::binAuthCheck($authorityId, $type);
    }

    //定义权限的常量
    public static function definedAuth()
    {
        if (!defined('NORMAL_USER')) define('NORMAL_USER', 1);
        if (!defined('DESIGNER_USER')) define('DESIGNER_USER', 2);
        if (!defined('ALBUM_USER')) define('ALBUM_USER', 3);
        if (!defined('FIX_TPL_USER')) define('FIX_TPL_USER', 4);
        if (!defined('GROUP_WORD_USER')) define('GROUP_WORD_USER', 5);
        if (!defined('AVATAR_USER')) define('AVATAR_USER', 6);
        if (!defined('SMART_USER')) define('SMART_USER', 7);
        if (!defined('DESIGNER_ASSET_USER')) define('DESIGNER_ASSET_USER', 8);
        if (!defined('DESIGNER_EDITOR_ASSET_USER')) define('DESIGNER_EDITOR_ASSET_USER', 9);
        if (!defined('DESIGNER_GIF_ASSET_USER')) define('DESIGNER_GIF_ASSET_USER', 10);
        if (!defined('FREE_DESIGNER_USER')) define('FREE_DESIGNER_USER', 11);
        if (!defined('ARTIFICIAL_DISTINCT_TEMPL_USER')) define('ARTIFICIAL_DISTINCT_TEMPL_USER', 12);
        if (!defined('COPY_TEMPLATE_USER')) define('COPY_TEMPLATE_USER', 13);
        if (!defined('PPT_DESIGNER_USER')) define('PPT_DESIGNER_USER', 14);
        if (!defined('DESIGNER_WITHOUT_PAGE_INFO')) define('DESIGNER_WITHOUT_PAGE_INFO', 15);
        if (!defined('GROUP_INTO_FIX')) define('GROUP_INTO_FIX', 16);
        if (!defined('GROUP_FIX_USER')) define('GROUP_FIX_USER', 17);
        if (!defined('ART_FIX_USER')) define('ART_FIX_USER', 18);
        if (!defined('DESIGNER_LOTTIE_USER')) define('DESIGNER_LOTTIE_USER', 19);
        if (!defined('DESIGNER_BG_VIDEO_USER')) define('DESIGNER_BG_VIDEO_USER', 20);
        if (!defined('DESIGNER_PSD_UPLOAD_USER')) define('DESIGNER_PSD_UPLOAD_USER', 21);
        if (!defined('IOS_ALBUM_USER')) define('IOS_ALBUM_USER', 22);
        if (!defined('RT_ASSET_USER')) define('RT_ASSET_USER', 23);
        if (!defined('RT_ASSET_FIX')) define('RT_ASSET_FIX', 24);
        if (!defined('TRY_DESIGNER_USER')) define('TRY_DESIGNER_USER', 25);
        if (!defined('DESIGNER_SY_ASSET_USER')) define('DESIGNER_SY_ASSET_USER', 26);

    }

    /**
     * 判断权限 二进制与运算
     *
     * @param int $authorityId 需要check权限的id(十进制)具体参考auth_func_id_list
     * @return bool
     */
    public static function binAuthCheck($authorityId, $userAuth = '', $getList = 0)
    {
        self::definedAuth();
        if (!isset(\Yii::$app->user->id)) return false;
        //$userAuth 用户数据库中存入的type(十进制)
        if (!$userAuth) {
            $userAuth = \Yii::$app->user->identity->type;
        }
        if (!$userAuth) return false;
        $authFuncIdList = [
            NORMAL_USER => self::longBin2dec(pow(10, 0)),//普通用户
            DESIGNER_USER => self::longBin2dec(pow(10, 1)),//设计师
            ALBUM_USER => self::longBin2dec(pow(10, 2)),//专辑兼职
            FIX_TPL_USER => self::longBin2dec(pow(10, 3)),//改图兼职
            GROUP_WORD_USER => self::longBin2dec(pow(10, 4)),//文案排版兼职
            AVATAR_USER => self::longBin2dec(pow(10, 5)),//纸娃娃初筛兼职
            SMART_USER => self::longBin2dec(pow(10, 6)),//智能生产兼职
            DESIGNER_ASSET_USER => self::longBin2dec(pow(10, 7)),//原创素材设计师
            DESIGNER_EDITOR_ASSET_USER => self::longBin2dec(pow(10, 8)),//设计师编辑器原创元素上传
            DESIGNER_GIF_ASSET_USER => self::longBin2dec(pow(10, 9)),//设计师GIF原创元素上传
            FREE_DESIGNER_USER => self::longBin2dec(pow(10, 10)),//设计师自由入口权限
            ARTIFICIAL_DISTINCT_TEMPL_USER => self::longBin2dec(pow(10, 11)),//人工识图兼职
            COPY_TEMPLATE_USER => self::longBin2dec(pow(10, 12)),//复制网站模板权限
            PPT_DESIGNER_USER => self::longBin2dec(pow(10, 13)),//PPT设计师权限
            DESIGNER_WITHOUT_PAGE_INFO => self::longBin2dec(pow(10, 14)),//设计师免添加分页信息权限
            GROUP_INTO_FIX => self::longBin2dec(pow(10, 15)),//前台组合字入翻新库权限
            GROUP_FIX_USER => self::longBin2dec(pow(10, 16)),//组合字翻新兼职
            ART_FIX_USER => self::longBin2dec(pow(10, 17)),//艺术字翻新兼职
            DESIGNER_LOTTIE_USER => self::longBin2dec(pow(10, 18)),//设计师原创动画上传
            DESIGNER_BG_VIDEO_USER => self::longBin2dec(pow(10, 19)),//设计师背景视频上传
            DESIGNER_PSD_UPLOAD_USER => self::longBin2dec(pow(10, 20)),//设计师psd模板上传
            IOS_ALBUM_USER => self::longBin2dec(pow(10, 21)),//ios专辑兼职
            RT_ASSET_USER => self::longBin2dec(pow(10, 22)),//富文本样式生产兼职
            RT_ASSET_FIX => self::longBin2decV2("1" . str_repeat("0", 23)),//富文本素材翻新兼职
            TRY_DESIGNER_USER => self::longBin2decV2("1" . str_repeat("0", 24)),//设计师试用
            DESIGNER_SY_ASSET_USER => self::longBin2decV2("1" . str_repeat("0", 25)),//原创摄影素材管理
        ];
        if (!$authFuncIdList[$authorityId]) return false;
        if ($getList == 1) return $authFuncIdList;
        if ($userAuth & $authFuncIdList[$authorityId]) {
            return true;
        }
        return false;
    }

    public static function longBin2dec($float)
    {
        $type = number_format($float . '', 0, '', '');
        return bindec($type);
    }

    public static function longBin2decV2($float)
    {
        return bindec($float);
    }

    public static function findAllAuthUser($quanxian)
    {
        $a = pow(2, $quanxian - 1);
        return $checker = User::find()
            ->select(['id', 'username'])
            ->where('(type & ' . $a . ')')
            ->indexBy('id')
            ->asArray()
            ->all();
    }

    //User表
    public static $dbUser = null;

    /**
     * 获取用户Authority对应的type
     * @param $uid
     * @param $authorityId
     * @return array
     */
    public static function getWebUserAuthority($uid, $authorityId)
    {
        self::$dbUser = User::findOne($uid);
        $type = self::$dbUser->type;
        $list = static::binAuthCheck($authorityId, '', 1);
        $userAuth = [];
        foreach ($list as $k => $v) {
            if ($type & $v) {
                $userAuth[] = $k;
            }
        }
        return $userAuth;
    }

    /**
     * 添加type对应的Authority
     * @param $uid
     * @param $authority
     * @param $authorityId
     * @return int
     */
    public static function addWebAuthority($uid, $authority)
    {
        $userAuth = static::getWebUserAuthority($uid, $authority);
        if (!in_array($authority, $userAuth)) {
            array_push($userAuth, $authority);
            $max = max($userAuth);
            $res = [];
            for ($i = 1; $i <= $max; $i++) {
                if (in_array($i, $userAuth)) {
                    $res[$i] = 1;
                } else {
                    $res[$i] = 0;
                }
            }
            $res = array_reverse($res);
            $type = implode("", $res);
            self::$dbUser->type = bindec($type);
            $rs = self::$dbUser->save();
            $redisKey = 'findIdentity:' . $uid;
            \Yii::$app->redis6->del($redisKey);
            return $rs;
        }
        return 1;
    }
}
