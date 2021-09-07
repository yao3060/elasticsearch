<?php

namespace app\components;
use Imagick;
use Yii;
use yii\helpers\HtmlPurifier;

/**
 * 工具类
 *
 * @property integer
 */
class Tools
{
    //获取用户的客户端ip
    public static function getUserIp()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');

        } elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function curl($url, $post = '', $cookie = '', $returnCookie = 0,$timeout=5, $noescape = 0, $ischeck_ssl = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if(!$ischeck_ssl) curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if ($post) {
            if (is_array($post)) {
                if($noescape == 1){
                    $post = json_encode($post,JSON_UNESCAPED_UNICODE);
                }else{
                    $post = json_encode($post);
                }
            } else {
                $post = http_build_query($post);
            }
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }
        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if ($returnCookie) {
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie'] = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        } else {
            return $data;
        }
    }
    public static function setRedis($db = 2, $key, $value, $time = 86400)
    {
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }
        $redis = 'redis' . $db;
        if ($time > 0) {
            $res = Yii::$app->$redis->set($key, $value, 'EX', $time);
        }else{
            //给定默认过期时间 为 REDIS_EXPIRE/86400 天
            $res = Yii::$app->$redis->set($key, $value, 'EX',REDIS_EXPIRE);
        }
        return $res;
    }

    public static function getRedis($db = 2, $key)
    {
        $redis = 'redis' . $db;
        $info = Yii::$app->$redis->get($key);
        if (!$info) return false;
        $return = unserialize($info);
        if ($return || $return === []) return $return;
        if (is_null(json_decode($info))) {
            return $info;
        } else {
            return json_decode($info, 1);
        }
    }

    public static function delRedis($db=2 ,$key)
    {
        $redis = 'redis' . $db;
        Yii::$app->$redis->del($key);
    }

    /**
     * 【字符串】一次性获取多个key的值
     * @param int $db
     * @param $keys
     * @return array|bool
     */
    public static function mgetRedis($db = 2, $keys) {
        if (count($keys) <= 0) return false;
        $redis = 'redis' . $db;
        $info = Yii::$app->$redis->executeCommand('MGET', $keys);
        if (!$info) return false;
        $res = [];
        foreach ($keys as $k => $keyName) {
            $return = unserialize($info[$k]);
            if ($return || $return === []) {
                $res[$keyName] = $return;
                continue;
            }
            if (is_null(json_decode($info[$k]))) {
                $res[$keyName] = $info[$k];
            } else {
                $res[$keyName] = json_decode($info[$k], 1);
            }
        }
        return $res;
    }

    /**
     * 设置cookid
     * @param $key
     * @param $value
     * @param int $time time这是一个UNIX时间戳，如果设置为0，或省略，该Cookie将在浏览器关闭时消失
     */
    public static function setCookie($key, $value, $time = 86400)
    {
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }
        // 在要发送的响应中添加一个新的 cookie
        Yii::$app->response->cookies->add(new \yii\web\Cookie([
            'name' => $key,
            'value' => $value,
            'expire' => $time,
        ]));
    }

    /**
     * 获取cookie
     * @param $key
     * @return \yii\web\Cookie|null
     */
    public static function getCookie($key)
    {
        $info = Yii::$app->response->cookies->getValue($key, '');
        if (!$info) return false;
        $return = unserialize($info);
        if ($return || $return === []) return $return;
        if (is_null(json_decode($info))) {
            return $info;
        } else {
            return json_decode($info, 1);
        }
    }

    /**
     * 删除cookie
     * @param $key
     */
    public static function delCookie($key)
    {
        Yii::$app->request->cookies->remove($key);
    }

    /**
     * 设置session
     * 过期时间 在配置内 3600*24*7
     * @param $key
     * @param $value
     */
    public static function setSession($key, $value)
    {
        $session = Yii::$app->session;
        // 检查session是否开启
        if ($session->isActive){
            // 开启session
            $session->open();
        }
        $session[$key] = $value;
    }
    /**
     * 获取session
     * @param $key
     * @return mixed|null
     */
    public static function getSession($key)
    {
        $session = Yii::$app->session;
        // 检查session是否开启
        if ($session->isActive){
            // 开启session
            $session->open();
        }
        return $session->get($key);
    }

    /**
     * 删除session
     * @param $key
     */
    public static function delSession($key)
    {
        $session = Yii::$app->session;
        // 检查session是否开启
        if ($session->isActive){
            // 开启session
            $session->open();
        }
        unset($session[$key]);
    }

    public static function splitKeyword($keyword)
    {
        $filterSymbol = ['，', '、', ',', '/', '&', '。', '（', '）', '(', ')', ';', '　', ' ', 'psd素材下载'];
        $keyword = str_replace($filterSymbol, ' ', $keyword);
        $keyword = array_filter(explode(' ', $keyword));
        foreach ($keyword as $k => $c) {
            if (mb_strlen($c) > 15) {
                unset($keyword[$k]);
            }
        }
        return $keyword;
    }

    public static function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );

            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }

        return false;
    }

    public static function isPay()
    {
        $uid = Yii::$app->user->id;
        if (!$uid) {
            return '0';
        }
        $session = Yii::$app->session;
        $uid = Yii::$app->user->id;
        $key = 'is_pay_' . $uid;
        $is_pay = (string)$session->get($key);
        if ($is_pay) {
            return $is_pay == 'yes' ? '1' : '0';
        }
        $find = UserPay::findOne(['uid' => $uid]);
        if ($find) {
            $session->set($key, 'yes');
            return '1';
        } else {
            $session->set($key, 'no');
            return '0';
        }
    }

    public static function clientProtocol($content)
    {
        header('Location:ipstoken://' . $content);
        exit();
    }

    public static function AB_test() {

        if (in_array($_GET['ab_test'], ['a', 'b'])) {
            return $_GET['ab_test'];
        }
        $num = explode('.', self::getTrack_id())[1];
        if (!$num || !is_numeric($num) || self::isSpider()) return "a";
        if ($num % 2 == 1) {
            return "a";
        } else {
            return "b";
        }
    }

    /**
     * Track_id 为基础的ab测
     * Track_id最后一位不是数字的算a
     * @param int $ratio    比例
     * 指的是b版本的比例 默认5（50%）
     * Track_id最后一位  以0开始的是b版本  例如比例5  则0，1，2，3，4 是b版本
     * @param $options      配置
     * $options = [
     *      'set_ab'=>[     按照uid单独设置用户显示的a版或b版  默认是测试的两个账号
     *          11333289=>'a',
     *          9667287=>'b'
     *      ]
     * ]
     * 生效级别 1 ab_test；2 get track_id ；3 set_ab ；4 系统 Track_id
     * @return mixed|string
     */
    public static function AB_testV2($ratio = 5,$options = ['set_ab'=>[11333289=>'a',9667287=>'b']]) {
        if (in_array($_GET['ab_test'], ['a', 'b'])) {
            return $_GET['ab_test'];
        }
        if(isset($_GET['track_id'])){
            $tkid = $_GET['track_id'];
        }else{
            if(isset($options['set_ab'])){
                if(@$options['set_ab'][@Yii::$app->user->id]){
                    return $options['set_ab'][@Yii::$app->user->id];
                }
            }
            $tkid = self::getTrack_id();
        }
        $num = substr(trim($tkid), -1);
        if (!is_numeric($num) || self::isSpider()) return "a";
        if ($num >= $ratio) {
            return "a";
        } else {
            return "b";
        }
    }

    public static function oldUserReward()
    {
        $user_id = Yii::$app->user->id;
        if (!$user_id) {
            return false;
        }
        $session = Yii::$app->session;
        $session_key = 'oldUserReward_showed' . $user_id;

        $created = IpsUserInfo::get('created', $user_id);
        $vip = IpsUserInfo::get('vip', $user_id);

        $days = date_diff(date_create(date('Y-m-d')), date_create($created))->days;
        if ($vip <= 1 && $days >= 4) {
//            $AB_test = self::AB_test();
            $AB_test = "b";//7-18 13:00 老用户ab测均返回B 享受立减活动
            if ($session->get($session_key)) {
                return false;
            }
            $session->set($session_key, 1);
            if (OldUserReward::saveRecord($AB_test)) {
                return $AB_test;
            }
        }
        return false;
    }

    public static function showAsset()
    {
        if (!Yii::$app->user->id) {
            return false;
        }
        static $vip = 0;
        if (!$vip) {
            $vip = IpsUserInfo::get('vip');
        }
        $show_vip = [4, 5, 8];
        if (in_array($vip, $show_vip)) {
            return true;
        }
        return false;
    }

    //需要监控PHP执行时间的 方法名
    public static function executeMethod()
    {
        return [
            'user-save-templ',
            'async-apply-download'
        ];
    }

    //判断远程图片是否存在
    public static function file_exists($url)
    {
        $fileExists = @file_get_contents($url, null, null, -1, 1) ? true : false;
        return $fileExists; //返回1，就说明文件存在。
    }

    public static function replaceSpecialChar($chars,$encoding = 'utf8')
    {
//        $regex = "/ |\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
//        return preg_replace($regex, "", $strParam);
        $pattern =($encoding=='utf8')?'/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u':'/[\x80-\xFF]/';
        preg_match_all($pattern,$chars,$result);
        $temp =join('',$result[0]);
        return $temp;
    }

    public static function getSceneTime()
    {
        static $time = 0;
        if (!$time) {
            $time = Yii::$app->redis7->get('other:scene');
        }
        return $time;
    }

    public static function getBankList()
    {
        return [
            1 => '中国工商银行',
            2 => '中国农业银行',
            3 => '中国银行',
            4 => '中国建设银行',
            5 => '中国邮政储蓄银行',
            6 => '广东发展银行',
            7 => '中国光大银行',
            8 => '交通银行',
            9 => '招商银行',
            10 => '兴业银行',
            11 => '平安银行（深发展）',
            12 => '中信银行',
            13 => '中国民生银行',
            14 => '上海浦东发展银行',
            15 => '华夏银行',
            16 => '北京银行',
            17 => '上海银行',
            18 => '宁波银行',
            19 => '广州银行',
            20 => '杭州银行'
        ];
    }

    public static function getAccountList()
    {
        return [
            1 => '借记卡(储蓄卡)',
            2 => '贷计卡'
        ];
    }

    /**
     * 申请设计师 -> 擅长品类
     * @return string[]
     */
    public static function getExpert(){
        return [
            1 => '新媒体（公众号首图、次图等）',
            2 => '印刷物料（竖版海报、宣传单等）',
            3 => '电商淘宝（主图、详情页等）',
            4 => 'GIF动图',
            5 => 'H5模版',
            6 => '视频模版',
            7 => '微信排版',
            8 => '原创插画',
            9 => '手机微信朋友圈（手机海报、日签、营销长图等）',
            10 => 'PPT模版',
            11 => '摄影素材',
        ];
    }

     public static function base64EncodeImage($image_file)
    {
        $base64_data = base64_encode(file_get_contents($image_file));
        return $base64_data;
    }


    //authcode加密函数
    //函数authcode($string, $operation, $key, $expiry)中的$string：字符串，明文或密文；$operation：ENCODE:加密 DECODE表示解密，其它表示加密；$key：密匙；$expiry：密文有效期。
    public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;

        // 密匙
        $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);

        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
        //解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :  sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&  substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }

    public static function http_post($url, $data_string)
    {
        $header[] = 'Content-Type:application/json;charset=utf-8';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public static function http_get($url, $header = [])
    {
        $header[] = 'Content-Type:application/json;charset=utf-8';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            print_r(curl_error($curl)).PHP_EOL;
        }
        curl_close($curl);
        return $data;
    }

    //二位数组内部 笛卡尔积
    public static function CartesianProduct($sets) {
        // 保存结果
        $result = array();
        // 循环遍历集合数据
        for ($i = 0, $count = count($sets); $i < $count - 1; $i++) {
            // 初始化
            if ($i == 0) {
                $result = $sets[$i];
            }
            // 保存临时数据
            $tmp = array();
            // 结果与下一个集合计算笛卡尔积
            foreach ($result as $res) {
                foreach ($sets[$i + 1] as $set) {
                    $tmp[] = $res . '_' . $set;
                }
            }
            // 将笛卡尔积写入结果
            $result = $tmp;
        }
        return $result;
    }

    //判断是否有可用抵扣券
    public static function availableIsShow($data)
    {
        foreach ($data as $k=>$v) {
            if($v['coupon_show']==10||$v['coupon_show']==20) {
                return false;
            }
        }
        return true;
    }

    public static function getProductCode($vipType) {
        $onlineVipInfo = UserVipType::allVipTypeInfo($vipType,1);
        $product_code = $onlineVipInfo['price'];
        return (int)$product_code;
    }

    public static function replaceFullSpecialChar($chars, $encoding = 'utf8') {
        $pattern = ($encoding == 'utf8') ? '/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u' : '/[\x80-\xFF]/';
        preg_match_all($pattern, $chars, $result);
        $temp = join('', $result[0]);
        return $temp;
    }

    public static function getWapEditUrl($tid,$userInfo,$user_source) {
        if(Yii::$app->user->isGuest) {
            $jump = "https://m.818ps.com/site/m_jump?tid=$tid";
            $jump = str_replace('&','$',str_replace('?','%',$jump));
            return "/site/m_login?jump=$jump";
        }else {
            return "https://h5.818ps.com/?id=$tid&userId=".$userInfo['id']."&user_source=$user_source&platform=wap";
        }
    }

    public static function getWapMyEditUrl($utid,$userInfo,$user_source) {
        return "https://h5.818ps.com/?uid=$utid&userId=".$userInfo['id']."&user_source=$user_source&platform=wap";
    }

    public static function isWap() {
        $isPhone = self::isMobile();

        if((Yii::$app->request->get('ia')==827 || $_COOKIE['ia'])) {
            return false;
        }
        if (($isPhone || Yii::$app->request->get('is_wap')==1)) {
            return true;
        }
        return false;
    }

    public function mb_str_split($str){
        return preg_split('/(?<!^)(?!$)/u', $str );
    }

    public function strong_replace($arr) {
        foreach ($arr as $k=>$v) {
            $data[] = '<###>'.$v.'</###>';
        }
        return $data;
    }

    public static function AnnualArr()
    {
        $list = [
            'xiqing' => [
                0 => [1343131,1343143,1343153,1343168,1343209,1343236,1319301,1319322,1319397,1319023],
                1 => [1343250,1343259,1343265,1343279,1343289,1343301,1314647,1314713,1314760,1314446],
                2 => [1348087,1348091,1348099,1348696,1349501,1350405,1331629,1331635,1331645,1331592],
                3 => [1351218,1351732,1351755,1351800,1351819,1351822,1331836,1331851,1331882,1331670],
                4 => [1352208,1352218,1352222,1352235,1352249,1352258,1332455,1332498,1332552,1332257],
                5 => [1352291,1352305,1352312,1352318,1352326,1352335,1332922,1332949,1332999,1332613],
                6 => [1353497,1353502,1353514,1353531,1353549,1353569,1338146,1338166,1338185,1337885],
                7 => [1353599,1353603,1353612,1353626,1353637,1353647,1338353,1338370,1338398,1338240],
                8 => [1353663,1353713,1353735,1353743,1353768,1353789,1339346,1339367,1339413,1339105],
                9 => [1353876,1353885,1353896,1353905,1353932,1353949,1340038,1340057,1340112,1339496],
                10 => [1355182,1355185,1355192,1355231,1355248,1355258,1355155,1355164,1355172,1326241],
                11 => [1355539,1355544,1355556,1355575,1355606,1355626,1355284,1355302,1355359,1341966],
                12 => [1357501,1357514,1357519,1357532,1357542,1357559,1357468,1357476,1357488,1334845],
                13 => [1358324,1358331,1358340,1358352,1358367,1358388,1358248,1358268,1358305,1331787],
                14 => [1360435,1360447,1360454,1360467,1360479,1360503,1360318,1360332,1360410,1338868],
                15 => [1315628,1362388,1315227,1315328,1315906,1315707,1315431,1315476,1315779,1314553]
            ],
            'shangwu' => [
                0 => [1343314,1343320,1343330,1343346,1343370,1346511,1325809,1325831,1325864,1325481],
                1 => [1346531,1346540,1346553,1346570,1346658,1346814,1326451,1326482,1326530,1325905],
                2 => [1346686,1346690,1346698,1346703,1346712,1346805,1327124,1327142,1327185,1326935],
                3 => [1346741,1346744,1346754,1346758,1346774,1346822,1327378,1327399,1327430,1327252],
                4 => [1346847,1346855,1346862,1346875,1346894,1346906,1328787,1328804,1328823,1328519],
                5 => [1346923,1346936,1346946,1347041,1347050,1347055,1328964,1328988,1329007,1328863],
                6 => [1347996,1348045,1348050,1348057,1348066,1348078,1331560,1331566,1331579,1331538],
                7 => [1352351,1352364,1352371,1352382,1352394,1352402,1337304,1337315,1337334,1337259],
                8 => [1357044,1357050,1357073,1357088,1357106,1357116,1357008,1357015,1357032,1331778],
                9 => [1358951,1358961,1358977,1358995,1359072,1359154,1358911,1358932,1358947,1334198],
                10 => [1359398,1359409,1359417,1359423,1359436,1359443,1359216,1359239,1359297,1345879],
                11 => [1354743,1354700,1354973,1354841,1358915,1354001,1354775,1354179,1354794,1359475],
                12 => [1357726,1357563,1354663,1355023,1352902,1352706,1352332,1352389,1352491,1353861],
                13 => [1355125,1355151,1352334,1352972,1352996,1354160,1353784,1353792,1354292,1355280],
                14 => [1358672,1357834,1358613,1358381,1358281,1358176,1357663,1357705,1358224,1340530],
                15 => [1320580,1362416,1320200,1320403,1320716,1365073,1320468,1320496,1320657,1316291],
                16 => [1355150,1355181,1360279,1360178,1355282,1355196,1355036,1355084,1355267,1346707]
            ],
            'keji' => [
                0 => [1352415,1352428,1352433,1352440,1352467,1352485,1337478,1337497,1337530,1337371],
                1 => [1356023,1356028,1356035,1356042,1356052,1356058,1355989,1356002,1356015,1334996],
                2 => [1356175,1356176,1356178,1356199,1356202,1356207,1356167,1356170,1356173,1335331],
                3 => [1357706,1357722,1357749,1357771,1357792,1357803,1357585,1357625,1357641,1332435],
                4 => [1358070,1358084,1358092,1358109,1358130,1358168,1358003,1358038,1358055,1331745],
                5 => [1359546,1359559,1359582,1359608,1359621,1359627,1359455,1359467,1359481,1332649],
                6 => [1359670,1359681,1359684,1359693,1359706,1359720,1359644,1359657,1359663,1327696],
                7 => [1355079,1355068,1354993,1355033,1354870,1353937,1355720,1354227,1355108,1325076],
                8 => [1354347,1354315,1354483,1354504,1354379,1354026,1354414,1354099,1354667,1339546],
                9 => [1326146,1362354,1325997,1326032,1326261,1326173,1326054,1326082,1326201,1323984],
                10 => [1354955,1354856,1354974,1360209,1354924,1354884,1354734,1354788,1354902,1346499],
                11 => [1359869,1359913,1360048,1360094,1360008,1359938,1359672,1359792,1359979,1355060]
            ],
            'zhongguo' => [
                0 => [1355101,1355105,1355110,1355117,1355129,1355138,1355064,1355074,1355095,1333493],
                1 => [1356231,1356235,1356238,1356243,1356244,1356248,1356212,1356219,1356228,1336508],
                2 => [1357404,1357405,1357414,1357421,1357438,1357444,1357313,1357327,1357341,1332083],
                3 => [1357910,1357917,1357925,1357942,1357963,1357970,1357850,1357859,1357900,1335112],
                4 => [1358533,1358548,1358562,1358581,1358601,1358617,1358435,1358459,1358496,1326599],
                5 => [1358690,1358694,1358701,1358785,1358837,1358850,1358652,1358668,1358679,1326014],
                6 => [1359786,1359799,1359805,1359825,1359837,1359861,1359728,1359745,1359775,1341031],
                7 => [1360117,1360121,1360128,1360138,1360156,1360165,1360027,1360043,1360103,1333225],
                8 => [1360235,1360246,1360253,1360264,1360281,1360294,1360190,1360203,1360227,1331605],
                9 => [1356953,1357035,1355411,1356504,1356750,1357304,1357190,1357299,1357613,1339338],
                10 => [1359306,1341309,1359462,1341371,1341420,1341530,1341443,1341464,1341511,1355111],
                11 => [1353415,1353427,1353435,1353448,1353462,1353476,1337727,1337752,1337819,1337613],
                12 => [1357501,1357514,1357519,1357532,1357542,1357559,1357468,1357476,1357488,1334845]
            ],
            'chuangyi' => [
                0 => [1355762,1355769,1355778,1355793,1355813,1355850,1355696,1355726,1355750,1320775],
                1 => [1356085,1356087,1356091,1356093,1356099,1356102,1356066,1356069,1356079,1332377],
                2 => [1356288,1356295,1356303,1356309,1356315,1356327,1356259,1356273,1356283,1347991],
                3 => [1356798,1356808,1356817,1356830,1356844,1356850,1356745,1356754,1356775,1342856],
                4 => [1356942,1356954,1356962,1356972,1356986,1356995,1356891,1356904,1356930,1328013],
                5 => [1357404,1357405,1357414,1357421,1357438,1357444,1357313,1357327,1357341,1332083],
                6 => [1357706,1357722,1357749,1357771,1357792,1357803,1357585,1357625,1357641,1332435],
                7 => [1355079,1355068,1354993,1355033,1354870,1353937,1355720,1354227,1355108,1325076],
                8 => [1354347,1354315,1354483,1354504,1354379,1354026,1354414,1354099,1354667,1339546]
            ],
            'liti' => [
                0 => [1355917,1355922,1355931,1355944,1355952,1355966,1355881,1355891,1355904,1335101],
                1 => [1356231,1356235,1356238,1356243,1356244,1356248,1356212,1356219,1356228,1336508],
                2 => [1357044,1357050,1357073,1357088,1357106,1357116,1357008,1357015,1357032,1331778],
                3 => [1358070,1358084,1358092,1358109,1358130,1358168,1358003,1358038,1358055,1331745],
                4 => [1358690,1358694,1358701,1358785,1358837,1358850,1358652,1358668,1358679,1326014],
                5 => [1359927,1359937,1359944,1359963,1359975,1359990,1359883,1359897,1359916,1353030],
                6 => [1360117,1360121,1360128,1360138,1360156,1360165,1360027,1360043,1360103,1333225],
                7 => [1360235,1360246,1360253,1360264,1360281,1360294,1360190,1360203,1360227,1331605],
                8 => [1355079,1355068,1354993,1355033,1354870,1353937,1355720,1354227,1355108,1325076],
                9 => [1354347,1354315,1354483,1354504,1354379,1354026,1354414,1354099,1354667,1339546],
                10 => [1359306,1341309,1359462,1341371,1341420,1341530,1341443,1341464,1341511,1355111],
                11 => [1348087,1348091,1348099,1348696,1349501,1350405,1331629,1331635,1331645,1331592],
                12 => [1357501,1357514,1357519,1357532,1357542,1357559,1357468,1357476,1357488,1334845]
            ],
        ];
        return $list;
    }

    public static function getAnnualBanner($style,$id)
    {
        $arr = [
            'xiqing' => [
                0 => 1,
                1 => 2,
                2 => 10,
                3 => 11,
                4 => 12,
                5 => 13,
                6 => 17,
                7 => 18,
                8 => 19,
                9 => 20,
                10 => 22,
                11 => 23,
                12 => 35,
                13 => 39,
                14 => 50,
                15 => 60
            ],
            'shangwu' => [
                0 => 3,
                1 => 4,
                2 => 5,
                3 => 6,
                4 => 7,
                5 => 8,
                6 => 9,
                7 => 14,
                8 => 33,
                9 => 42,
                10 => 43,
                11 => 51,
                12 => 54,
                13 => 55,
                14 => 58,
                15 => 61,
                16 => 63,
            ],
            'keji' => [
                0 => 15,
                1 => 26,
                2 => 28,
                3 => 36,
                4 => 38,
                5 => 44,
                6 => 45,
                7 => 52,
                8 => 53,
                9 => 59,
                10 => 62,
                11 => 64,
            ],
            'zhongguo' => [
                0 => 21,
                1 => 29,
                2 => 34,
                3 => 37,
                4 => 40,
                5 => 41,
                6 => 46,
                7 => 48,
                8 => 49,
                9 => 56,
                10 => 57,
                11 => 16,
                12 => 35,
            ],
            'chuangyi' => [
                0 => 24,
                1 => 27,
                2 => 30,
                3 => 31,
                4 => 32,
                5 => 34,
                6 => 36,
                7 => 52,
                8 => 53,
            ],
            'liti' => [
                0 => 25,
                1 => 29,
                2 => 33,
                3 => 38,
                4 => 41,
                5 => 47,
                6 => 48,
                7 => 49,
                8 => 52,
                9 => 53,
                10 => 57,
                11 => 10,
                12 => 35,
            ]
        ];
        return $arr[$style][$id-1];
    }

    public static function getAnnualTitle()
    {
        $arr = [
            'xiqing' => [
                0 => '2019乘风破浪 开拓未来',
                1 => '2019赢天下',
                2 => '年终答谢会',
                3 => '喜庆剪纸风猪年2019',
                4 => '2019新年主题',
                5 => '2019颁奖晚会',
                6 => '筑梦未来 再创辉煌',
                7 => '猪年行大运',
                8 => '销量战报',
                9 => '荣耀2019',
                10 => '年度盛典',
                11 => '颁奖典礼',
                12 => '中国风年终答谢会',
                13 => '中国风年终盛典',
                14 => '工作总结年度盛典',
                15 => '年会盛典'
            ],
            'shangwu' => [
                0 => '迎战猪年',
                1 => '跨越2019',
                2 => '再创辉煌',
                3 => '荣耀起航',
                4 => '开门红',
                5 => '迎战新猪年',
                6 => '颁奖盛典',
                7 => '新跨越 新起点',
                8 => '感恩2018 迎战2019',
                9 => '2018跨年盛典',
                10 => '一路芳华 浓情感恩',
                11 => '不忘初心 勇创未来',
                12 => '携手共赢 引领未来',
                13 => '梦想起航 共创未来',
                14 => '2019乘风破浪',
                15 => '企业年会年终盛典',
                16 => '无奋斗 不青春',
            ],
            'keji' => [
                0 => '携手共赢 梦想起航',
                1 => '凝聚拼搏 辉煌卓越',
                2 => '扬帆起航 共赢未来',
                3 => '为梦想加油',
                4 => '梦想起航 共创辉煌',
                5 => '聚梦想 创辉煌',
                6 => '科技 新跨越新梦想',
                7 => '新跨越 新梦想',
                8 => '梦想在这里起航',
                9 => '年度盛典',
                10 => '预见未来',
                11 => '2019扬帆起航 共赢未来',
            ],
            'zhongguo' => [
                0 => '2019中国风年度盛典',
                1 => '新年心一起',
                2 => '颁奖盛典',
                3 => '猪年大吉',
                4 => '携手2019',
                5 => '乘风破浪 开拓未来',
                6 => '梦想腾飞 筑梦猪年',
                7 => '不忘初心 再创辉煌',
                8 => '新征程 新跨越',
                9 => '新梦想 新征程',
                10 => '乘梦飞翔 共创辉煌',
                11 => '绽放2019',
                12 => '中国风年终答谢会',
            ],
            'chuangyi' => [
                0 => '筑梦未来',
                1 => '预见未来',
                2 => '2019预见未来',
                3 => '为梦想释放狂野',
                4 => '遇见未来 梦想在这里起航',
                5 => '颁奖盛典',
                6 => '为梦想加油',
                7 => '新跨越 新梦想',
                8 => '梦想在这里起航'
            ],
            'liti' => [
                0 => '2019猪年大吉',
                1 => '新年心一起',
                2 => '感恩2018 迎战2019',
                3 => '梦想起航 共创辉煌',
                4 => '乘风破浪 开拓未来',
                5 => '展望2019',
                6 => '不忘初心 再创辉煌',
                7 => '新征程 新跨越',
                8 => '新跨越 新梦想',
                9 => '梦想在这里起航',
                10 => '乘梦飞翔 共创辉煌',
                11 => '年终答谢会',
                12 => '中国风年终答谢会',
            ]
        ];
        return $arr;
    }

    public static function lotteryArr() {

        return [
            1=>['goods_name'=>'100积分','back_zs'=>0,'back_other'=>0],
            2=>['goods_name'=>'200积分','back_zs'=>0,'back_other'=>0],
            3=>['goods_name'=>'300积分','back_zs'=>0,'back_other'=>0],
            4=>['goods_name'=>'400积分','back_zs'=>0,'back_other'=>0],
            5=>['goods_name'=>'再来一次','back_zs'=>3,'back_other'=>3],
            6=>['goods_name'=>'600积分','back_zs'=>0,'back_other'=>0],
            7=>['goods_name'=>'700积分','back_zs'=>0,'back_other'=>0],
            8=>['goods_name'=>'800积分','back_zs'=>0,'back_other'=>0],
            9=>['goods_name'=>'900积分','back_zs'=>0,'back_other'=>0],
            10=>['goods_name'=>'1000积分','back_zs'=>0,'back_other'=>0],
            11=>['goods_name'=>'2000积分','back_zs'=>0,'back_other'=>0],
            12=>['goods_name'=>'5000积分','back_zs'=>0,'back_other'=>0],
            13=>['goods_name'=>'10000积分','back_zs'=>5,'back_other'=>-1],
            14=>['goods_name'=>'9.5折','back_zs'=>-1,'back_other'=>6],
            15=>['goods_name'=>'9折','back_zs'=>-1,'back_other'=>6],
            16=>['goods_name'=>'8.5折','back_zs'=>-1,'back_other'=>6],
            17=>['goods_name'=>'8折','back_zs'=>-1,'back_other'=>6],
            18=>['goods_name'=>'至尊VIP','back_zs'=>7,'back_other'=>7],
            19=>['goods_name'=>'终身VIP','back_zs'=>2,'back_other'=>2],
            20=>['goods_name'=>'摹客90天VIP','back_zs'=>6,'back_other'=>5],
            21=>['goods_name'=>'包图7天VIP','back_zs'=>4,'back_other'=>4],
            22=>['goods_name'=>'包图15天VIP','back_zs'=>4,'back_other'=>4],
            23=>['goods_name'=>'包图30天VIP','back_zs'=>4,'back_other'=>4],
            24=>['goods_name'=>'千图1天VIP','back_zs'=>1,'back_other'=>1],
            25=>['goods_name'=>'千图3天VIP','back_zs'=>1,'back_other'=>1],
            26=>['goods_name'=>'千图7天VIP','back_zs'=>1,'back_other'=>1],
        ];
    }

    public static function KmhArr()
    {
        $list = [
            'zg'=>[
                0=>['ids'=>[1506389,1505975,1505963,1506433,1506460,1505419,1506474,1506487,1505990,1506406],'title'=>'红色剪纸风开门大吉开业','banner_id'=>28,'sort'=>1],
                1=>['ids'=>[1503290,1503085,1503062,1503345,1503368,1502991,1503384,1503408,1503102,1503303],'title'=>'卡通插画风喜庆开门大吉开业模板','banner_id'=>26,'sort'=>3],
                2=>['ids'=>[1528710,1528690,1528677,1528724,1528747,1528655,1528782,1528864,1528704,1528719],'title'=>'红色大气开门大吉模板','banner_id'=>33,'sort'=>8],
                3=>['ids'=>[1529207,1529192,1529131,1529227,1529251,1528885,1529268,1529277,1529195,1529216],'title'=>'企业开门大吉开门红','banner_id'=>34,'sort'=>34],
                4=>['ids'=>[1528621,1528615,1528607,1528628,1528633,1528571,1528637,1528638,1528618,1528623],'title'=>'中国风开门大吉大气模板','banner_id'=>32,'sort'=>33],
                5=>['ids'=>[1502553,1502457,1502424,1502569,1502620,1502079,1502673,1502940,1502469,1502558],'title'=>'国风企业开门大吉开业','banner_id'=>25,'sort'=>30],
                6=>['ids'=>[1503580,1503549,1503536,1503620,1503644,1503460,1503672,1503704,1503562,1503598],'title'=>'大气红色插画风开门大吉开业','banner_id'=>27,'sort'=>10],
                7=>['ids'=>[1519982,1519971,1519964,1520016,1520032,1519953,1520046,1520037,1519976,1519985],'title'=>'红色新年大气开门大吉','banner_id'=>31,'sort'=>32],
                8=>['ids'=>[1501367,1501362,1501356,1501378,1501393,1501334,1501389,1501398,1501364,1501370],'title'=>'中国风红色开门大吉','banner_id'=>22,'sort'=>27],
                9=>['ids'=>[1495486,1495440,1495148,1495690,1495063,1492648,1495758,1495955,1495459,1495515],'title'=>'红金中国风企业开门大吉','banner_id'=>19,'sort'=>25],
                10=>['ids'=>[1484207,1484188,1484180,1484354,1494567,1476498,1484374,1493420,1484196,1484317],'title'=>'开门红','banner_id'=>2,'sort'=>16],
                11=>['ids'=>[1494884,1494827,1494812,1494913,1494938,1490215,1494926,1495789,1494835,1494894],'title'=>'企业开门红开业模板','banner_id'=>11,'sort'=>19],
                12=>['ids'=>[1484067,1484023,1484006,1484121,1494534,1476323,1484130,1493404,1484044,1484088],'title'=>'中国风红色开门大吉开门红','banner_id'=>1,'sort'=>15],
           ],
            'hj'=>[
                0=>['ids'=>[1495464,1495431,1495097,1495616,1494966,1490394,1495699,1495768,1495442,1495489],'title'=>'大气黑金企业开门大吉开业','banner_id'=>12,'sort'=>2],
                1=>['ids'=>[1495481,1495437,1495131,1495672,1495042,1492168,1495741,1495915,1495455,1495511],'title'=>'企业开门大吉','banner_id'=>17,'sort'=>9],
                2=>['ids'=>[1495484,1495438,1495144,1495681,1495050,1492405,1495749,1495936,1495458,1495513],'title'=>'企业开门红开门大吉开业','banner_id'=>18,'sort'=>24],
            ],
            'ch'=>[
                0=>['ids'=>[1503290,1503085,1503062,1503345,1503368,1502991,1503384,1503408,1503102,1503303],'title'=>'卡通插画风喜庆开门大吉开业模板','banner_id'=>26,'sort'=>3],
                1=>['ids'=>[1528710,1528690,1528677,1528724,1528747,1528655,1528782,1528864,1528704,1528719],'title'=>'红色大气开门大吉模板','banner_id'=>33,'sort'=>8],
                2=>['ids'=>[1503580,1503549,1503536,1503620,1503644,1503460,1503672,1503704,1503562,1503598],'title'=>'大气红色插画风开门大吉开业','banner_id'=>27,'sort'=>10],
                3=>['ids'=>[1528621,1528615,1528607,1528628,1528633,1528571,1528637,1528638,1528618,1528623],'title'=>'中国风开门大吉大气模板','banner_id'=>32,'sort'=>33],
                4=>['ids'=>[1502553,1502457,1502424,1502569,1502620,1502079,1502673,1502940,1502469,1502558],'title'=>'国风企业开门大吉开业','banner_id'=>25,'sort'=>30],
                5=>['ids'=>[1494884,1494827,1494812,1494913,1494938,1490215,1494926,1495789,1494835,1494894],'title'=>'企业开门红开业模板','banner_id'=>11,'sort'=>19],
                6=>['ids'=>[1529207,1529192,1529131,1529227,1529251,1528885,1529268,1529277,1529195,1529216],'title'=>'企业开门大吉开门红','banner_id'=>34,'sort'=>34],
                7=>['ids'=>[1506589,1506548,1506495,1506637,1506664,1505571,1506678,1506709,1506569,1506612],'title'=>'中国风开门大吉开业模板','banner_id'=>29,'sort'=>31],
                8=>['ids'=>[1501495,1501458,1501447,1501535,1501559,1501414,1501550,1501577,1501467,1501524],'title'=>'简约插画开业大吉','banner_id'=>23,'sort'=>28],
                9=>['ids'=>[1519982,1519971,1519964,1520016,1520032,1519953,1520046,1520037,1519976,1519985],'title'=>'红色新年大气开门大吉','banner_id'=>31,'sort'=>32],
            ],
            'kj'=>[
                0=>['ids'=>[1488149,1488112,1488097,1488180,1494756,1482401,1488199,1493642,1488118,1488170],'title'=>'立体创意风开业酬宾开门红','banner_id'=>9,'sort'=>4],
                1=>['ids'=>[1486199,1485837,1485823,1486229,1494675,1481093,1486247,1493529,1486184,1486217],'title'=>'科技风企业开门红开业','banner_id'=>6,'sort'=>11],
                2=>['ids'=>[1499003,1498978,1498963,1499053,1499025,1498872,1499084,1499095,1498986,1499018],'title'=>'粉色科技风大气开门大吉','banner_id'=>20,'sort'=>26],
                3=>['ids'=>[1485209,1484773,1484754,1485237,1494595,1479557,1485243,1493454,1485201,1485227],'title'=>'开门红开业','banner_id'=>4,'sort'=>17],
                4=>['ids'=>[1484686,1484651,1484618,1484713,1494584,1479210,1484733,1493446,1484663,1484699],'title'=>'开业大吉开门红','banner_id'=>35,'sort'=>35],
            ],
            'jz'=>[
                0=>['ids'=>[1506389,1505975,1505963,1506433,1506460,1505419,1506474,1506487,1505990,1506406],'title'=>'红色剪纸风开门大吉开业','banner_id'=>28,'sort'=>1],
                1=>['ids'=>[1501783,1501733,1501708,1501860,1501916,1501607,1501893,1501936,1501765,1501797],'title'=>'剪纸风红色喜庆开门大吉','banner_id'=>24,'sort'=>29],
                2=>['ids'=>[1485270,1485260,1485256,1485283,1494622,1479810,1485287,1493469,1485263,1485275],'title'=>'剪纸风开门红开业','banner_id'=>5,'sort'=>18],
            ],
            'lt'=>[
                0=>['ids'=>[1488346,1488309,1488295,1488401,1494771,1482609,1488420,1493666,1488344,1488379],'title'=>'立体创意开门红企业开业','banner_id'=>10,'sort'=>5],
                1=>['ids'=>[1484442,1484409,1484395,1484506,1494578,1476741,1484532,1493436,1484420,1484476],'title'=>'5d插画开门红','banner_id'=>3,'sort'=>12],
                2=>['ids'=>[1510155,1510041,1510013,1510105,1510138,1509890,1510225,1510279,1510061,1510090],'title'=>'中国风蓝色简约开门大吉','banner_id'=>30,'sort'=>14],
                3=>['ids'=>[1495474,1495435,1495111,1495643,1495025,1491030,1495730,1495836,1495451,1495501],'title'=>'立体创意风企业开门大吉开业','banner_id'=>15,'sort'=>22],
                4=>['ids'=>[1495478,1495436,1495121,1495657,1495034,1491980,1495739,1495898,1495453,1495505],'title'=>'企业开门红开业开工大吉','banner_id'=>16,'sort'=>23],
            ],
            'lsjb'=>[
                0=>['ids'=>[1487168,1487102,1487091,1487238,1494730,1481597,1487269,1493594,1487124,1487215],'title'=>'立体创意开门红企业开业','banner_id'=>7,'sort'=>6],
                1=>['ids'=>[1487752,1487696,1487293,1487806,1494743,1481819,1487838,1493628,1487719,1487784],'title'=>'开门红企业开业','banner_id'=>8,'sort'=>13],
                2=>['ids'=>[1495469,1495433,1495104,1495632,1495020,1490776,1495725,1495821,1495450,1495496],'title'=>'创意渐变企业开门大吉开业','banner_id'=>14,'sort'=>21],
                3=>['ids'=>[1495466,1495432,1495102,1495627,1494977,1490663,1495715,1495810,1495446,1495494],'title'=>'渐变创意风企业开门大吉开业','banner_id'=>13,'sort'=>20],
            ],
            'cy'=>[
                0=>['ids'=>[1499292,1499251,1499231,1499312,1499348,1499172,1499328,1499362,1499260,1499299],'title'=>'波普风简约卡通黄色开门大吉','banner_id'=>21,'sort'=>7],
                1=>['ids'=>[1510155,1510041,1510013,1510105,1510138,1509890,1510225,1510279,1510061,1510090],'title'=>'中国风蓝色简约开门大吉','banner_id'=>30,'sort'=>14],
                2=>['ids'=>[1487168,1487102,1487091,1487238,1494730,1481597,1487269,1493594,1487124,1487215],'title'=>'立体创意开门红企业开业','banner_id'=>7,'sort'=>6],
                3=>['ids'=>[1488346,1488309,1488295,1488401,1494771,1482609,1488420,1493666,1488344,1488379],'title'=>'立体创意开门红企业开业','banner_id'=>10,'sort'=>5],
                4=>['ids'=>[1488149,1488112,1488097,1488180,1494756,1482401,1488199,1493642,1488118,1488170],'title'=>'立体创意风开业酬宾开门红','banner_id'=>9,'sort'=>4],
                5=>['ids'=>[1528621,1528615,1528607,1528628,1528633,1528571,1528637,1528638,1528618,1528623],'title'=>'中国风开门大吉大气模板','banner_id'=>32,'sort'=>33],
                6=>['ids'=>[1494884,1494827,1494812,1494913,1494938,1490215,1494926,1495789,1494835,1494894],'title'=>'企业开门红开业模板','banner_id'=>11,'sort'=>19],
            ]
        ];
        return $list;
    }

    public static function RecruitArr()
    {
        $list = [
            'sw'=>[
                0=>['ids'=>[1631284,1631540,1631186,1632106,1632227,1632246,1637406,1631881,1630965,1633314],'title'=>' 2019春季招聘几何拼接商务风模板','banner_id'=>3,'sort'=>3],
                1=>['ids'=>[1626187,1626191,1626764,1626801,1626874,1626970,1630539,1630384,1627012,1627044],'title'=>'商务风春季招聘应届生校园招聘模板','banner_id'=>5,'sort'=>5],
                2=>['ids'=>[1636846,1636748,1637801,1637846,1637034,1637776,1637736,1637400,1637037,1637835],'title'=>'春季招聘黑色山水江湖寻人中国风模板','banner_id'=>18,'sort'=>18],
                3=>['ids'=>[1631240,1631152,1631523,1631367,1631335,1631413,1631389,1631557,1631341,1631362],'title'=>'创意灯泡商务风招聘模板','banner_id'=>8,'sort'=>8],
                4=>['ids'=>[1637880,1638133,1637970,1638010,1638417,1637932,1637594,1637580,1637414,1637990],'title'=>'春招招聘会创意商务风模板','banner_id'=>19,'sort'=>19],
                5=>['ids'=>[1635353,1635676,1635729,1635992,1635975,1635989,1636642,1637666,1636692,1636658],'title'=>'春招诚聘招聘镭射渐变模板','banner_id'=>14,'sort'=>14],
            ],
            'ch'=>[
                0=>['ids'=>[1633181,1632453,1633195,1633206,1633250,1633218,1633243,1633237,1633251,1633249],'title'=>'春季招聘寻人放大镜插画创意模板','banner_id'=>1,'sort'=>1],
                1=>['ids'=>[1630485,1625423,1625842,1625862,1625518,1625600,1638653,1628552,1627215,1630162],'title'=>'春季招聘春招原创元素插画风模板','banner_id'=>4,'sort'=>4],
                2=>['ids'=>[1635353,1635676,1635729,1635992,1635975,1635989,1636642,1637666,1636692,1636658],'title'=>'春招诚聘招聘镭射渐变模板','banner_id'=>14,'sort'=>14],
                3=>['ids'=>[1637561,1636804,1637757,1637392,1637015,1637576,1637022,1637646,1637018,1637367],'title'=>'春季招聘插画中国风模板','banner_id'=>16,'sort'=>16],
            ],
            'zg'=>[
                0=>['ids'=>[1636846,1636748,1637801,1637846,1637034,1637776,1637736,1637400,1637037,1637835],'title'=>'春季招聘黑色山水江湖寻人中国风模板','banner_id'=>18,'sort'=>18],
                1=>['ids'=>[1632139,1631269,1633120,1632426,1632386,1632955,1633233,1633191,1633246,1632919],'title'=>'2019春季招聘宣传水墨中式模板','banner_id'=>12,'sort'=>12],
                2=>['ids'=>[1632930,1633067,1632958,1632981,1633052,1632378,1632912,1632732,1633101,1633006],'title'=>'中国风喜庆成套春季校园招聘模板','banner_id'=>15,'sort'=>15],
                3=>['ids'=>[1637561,1636804,1637757,1637392,1637015,1637576,1637022,1637646,1637018,1637367],'title'=>'春季招聘插画中国风模板','banner_id'=>16,'sort'=>16],
            ],
            'lt'=>[
                0=>['ids'=>[1626700,1626545,1626669,1626638,1626603,1626757,1626728,1626739,1626585,1626614],'title'=>'炫酷立体渐变风2019招聘模板','banner_id'=>9,'sort'=>9],
                1=>['ids'=>[1631383,1545311,1631411,1631424,1631442,1631824,1632467,1637572,1632904,1632997],'title'=>'诚聘人才春招渐变立体风模板','banner_id'=>13,'sort'=>13],
                2=>['ids'=>[1635353,1635676,1635729,1635992,1635975,1635989,1636642,1637666,1636692,1636658],'title'=>'春招诚聘招聘镭射渐变模板','banner_id'=>14,'sort'=>14],
            ],
            'jb'=>[
                0=>['ids'=>[1625686,1625902,1625880,1625951,1627216,1627229,1628579,1627288,1627654,1627672],'title'=>'简洁春招几何蓝紫渐变模板','banner_id'=>6,'sort'=>6],
                1=>['ids'=>[1635679,1635982,1633217,1634780,1633300,1636636,1635414,1632411,1636744,1633332],'title'=>'2019春招校园招聘渐变风模板','banner_id'=>7,'sort'=>7],
                2=>['ids'=>[1626700,1626545,1626669,1626638,1626603,1626757,1626728,1626739,1626585,1626614],'title'=>'炫酷立体渐变风2019招聘模板','banner_id'=>9,'sort'=>9],
                3=>['ids'=>[1633104,1636964,1636946,1636972,1636937,1636985,1636954,1637586,1637773,1636994],'title'=>'蓝色创意渐变风招聘模板','banner_id'=>17,'sort'=>17],
                4=>['ids'=>[1633132,1633164,1633145,1633151,1633163,1633142,1633135,1633167,1633061,1633155],'title'=>'春季招聘创意渐变风模板','banner_id'=>10,'sort'=>10],
                5=>['ids'=>[1638651,1637934,1638678,1638829,1638014,1638857,1638903,1638871,1638886,1638890],'title'=>'蓝色创意渐变风招聘模板','banner_id'=>21,'sort'=>21],
                6=>['ids'=>[1624122,1623953,1624164,1624192,1624203,1624211,1624221,1624276,1624244,1624215],'title'=>'春季招聘宣讲会粉紫色渐变镭射模板','banner_id'=>20,'sort'=>20],
                7=>['ids'=>[1631383,1545311,1631411,1631424,1631442,1631824,1632467,1637572,1632904,1632997],'title'=>'诚聘人才春招渐变立体风模板','banner_id'=>13,'sort'=>13],
            ],
            'cy'=>[
                0=>['ids'=>[1632186,1631121,1633382,1634915,1633534,1633298,1638631,1633340,1631399,1634227],'title'=>'春招春季招聘创意模板','banner_id'=>2,'sort'=>2],
                1=>['ids'=>[1631240,1631152,1631523,1631367,1631335,1631413,1631389,1631557,1631341,1631362],'title'=>'创意灯泡商务风招聘模板','banner_id'=>8,'sort'=>8],
                2=>['ids'=>[1633132,1633164,1633145,1633151,1633163,1633142,1633135,1633167,1633061,1633155],'title'=>'春季招聘创意渐变风模板','banner_id'=>10,'sort'=>10],
                3=>['ids'=>[1633104,1636964,1636946,1636972,1636937,1636985,1636954,1637586,1637773,1636994],'title'=>'蓝色创意渐变风招聘模板','banner_id'=>17,'sort'=>17],
                4=>['ids'=>[1637880,1638133,1637970,1638010,1638417,1637932,1637594,1637580,1637414,1637990],'title'=>'春招招聘会创意商务风模板','banner_id'=>19,'sort'=>19],
                5=>['ids'=>[1624122,1623953,1624164,1624192,1624203,1624211,1624221,1624276,1624244,1624215],'title'=>'春季招聘宣讲会粉紫色渐变镭射模板','banner_id'=>20,'sort'=>20],
                6=>['ids'=>[1638651,1637934,1638678,1638829,1638014,1638857,1638903,1638871,1638886,1638890],'title'=>'蓝色创意渐变风招聘模板','banner_id'=>21,'sort'=>21],
                7=>['ids'=>[1636225,1636651,1636639,1636468,1636632,1632410,1635994,1635705,1636197,1636623],'title'=>'春季招聘手机海报创意风蓝色模板','banner_id'=>11,'sort'=>11],
            ],
        ];
        return $list;
    }


    public static function checkNewYearShow()
    {
        return $_COOKIE['new-year'];
    }

    /**
     * 获取字母 a-z字母列表
     */
    public static function getAlphabet()
    {
        foreach (range('A','Z') as $word){
            $range[] = $word;
        }
        return $range;
    }

    public static function impressionVideo()
    {
        return json_encode([
            1=>['id'=>'5285890786128393561','title'=>'如何应付暴走老板','des'=>'手握图怪兽，面对老板一点不慌，招聘海报一秒完成'],
            2=>['id'=>'5285890786155484355','title'=>'菜单太丑，我想分手！','des'=>'情侣因为菜单太丑而闹别扭，还好图怪兽出来救场'],
            3=>['id'=>'5285890786152362342','title'=>'小编不加班秘笈','des'=>'用了图怪兽分分钟搞定封面图，再也不会没时间陪女朋友了'],
            4=>['id'=>'5285890786152500011','title'=>'命中注定图怪兽','des'=>'确认过眼神，图怪兽就是让你升职加薪的做图神器'],
            5=>['id'=>'5285890786278815988','title'=>'新手入门—登录篇','des'=>'简单快速了解图怪兽的登录方式'],
            6=>['id'=>'5285890786278674452','title'=>'新手入门—文字篇','des'=>'文字功能操作有问题？这个攻略了解一下'],
            7=>['id'=>'5285890786241322707','title'=>'新手入门','des'=>'简单快速了解图怪兽'],
            8=>['id'=>'5285890786925245830','title'=>'新手入门—画布篇','des'=>'创建画布遇到问题怎么办？点击查看解决方案'],
        ]);
    }

    public static function getVipKeyName($onlineVipInfo,$vipType)
    {
        switch ($onlineVipInfo[$vipType]['classify']) {//1全站  3gif  默认图片
            case 1:
                $expire_name = 'expire';
                $vip_type_name = 'vip_type';
                $amount_left = 'amount_left';
                $amount_name = 'amount_max';
                $userInfoVipName = 'vip';
                break;
            case 3:
                $expire_name = 'expire_gif';
                $vip_type_name = 'vip_type_gif';
                $amount_left = 'amount_left_gif';
                $amount_name = 'amount_max_gif';
                $userInfoVipName = 'vip_gif';
                break;
            case 2:
                $expire_name = 'expire_pic';
                $vip_type_name = 'vip_type_pic';
                $amount_left = 'amount_left_pic';
                $amount_name = 'amount_max_pic';
                $userInfoVipName = 'vip_pic';
                break;
            default:
                $expire_name = 'expire_video';
                $vip_type_name = 'vip_type_video';
                $amount_left = 'amount_left_video';
                $amount_name = 'amount_max_other';
                $userInfoVipName = 'vip_video';
        }

        return [$expire_name,$vip_type_name,$amount_left,$amount_name,$userInfoVipName];
    }

    public static function roastAsset($type='',$pages=1)
    {
        $arr = [
            //背景
            1 => [36584482, 36583616, 36583808, 36585200, 36584484, 36582738, 36582738, 36585252, 36585938, 36582898, 34617354, 34608641, 34620145, 34533574, 34528935, 34619444, 34619437, 34594831, 34598069, 34522895, 34575325, 34617924, 34511167, 34579231, 34592988, 34597674, 34610617, 34617503, 36585200, 34575018, 34562126, 34575346, 34560971, 34545144, 34617927, 34523720, 34591818, 34575337],
            //办公
            2 => [36585179, 36585178, 36585196, 36585194, 36582204, 36585191, 36585190, 36585189, 36585253, 36585256, 36581570, 36584828, 36584985, 36584832, 36584831, 36584830, 36584829, 34507185, 34721947, 34495142, 34705224, 34705220, 34705167, 34711799, 34711818, 34711804, 34766899, 34641787, 34737038, 34535466, 34614336, 34721514, 34668845, 34628316, 36585256, 34677811],
            //装饰
            3 => [34550126, 34520420, 34706162, 34497800, 34739472, 34766640, 34498189, 34736968, 34739304, 34764263, 34708440, 34499391, 34711917, 34729697, 34739470, 34605794, 34620326, 34632478, 34636783, 34620321, 34620324, 34535703, 34762619, 34755703, 34658331, 34753117, 34736905, 34730117, 34730119, 34738454, 34734889, 34749807],
            //居家
            4 => [36582908, 36584585, 36584584, 36583888, 36583887, 36583133, 36583105, 36583104, 36583103, 36579618, 36579617, 36579616, 36579615, 36579613, 36579442, 36579406, 36577703, 36577702, 36577701, 36577700, 36577699, 36577697, 36577696, 36577695, 36577694, 36577686, 36577685, 36577684, 36577683, 36583829, 36583614, 36584369, 36583885, 36583144, 36583085, 36583045, 36581585],
            //任务
            5 => [34686352, 34693264, 34673124, 34494978, 34745721, 34603706, 34511329, 34525080, 34674254, 34674256, 34745722, 34615776, 34493193, 34493116, 34686454, 34540035, 34659679, 34501503, 34714921, 34714922, 34715414, 34725573, 34652296, 34535403, 36583540, 34498959, 36583535, 36583541, 34501286, 34631146, 34631144, 34674255, 34501871, 34516767],
            //文案
            6 => [
                ['title' => "Yeah，又可以挣钱了！"],
                ['title' => "加倍的工作，加倍的快乐"],
                ['title' => "工作，让周一更美好"],
                ['title' => "快乐无比，嘴角胡乱上扬"],
                ['title' => "像一棵海草海草，随风飘摇"],
                ['title' => "今天上完班，明天还想上"],
                ['title' => "凹个造型，换个心情"],
                ['title' => "要上班了好开心"],
                ['title' => "今天的生活也是同样的苦涩"],
                ['title' => "新的一天，新的难过"],
                ['title' => "生无可恋"],
                ['title' => "敲键盘的手微微颤抖"],
                ['title' => "哇地一声哭出来"],
                ['title' => "心里有一场海啸"],
                ['title' => "佛系上班，不悲不喜"],
                ['title' => "会呼吸的痛"],
                ['title' => "流下了PH值=10的泪水"],
                ['title' => "宝宝委屈，但宝宝不说"],
                ['title' => "喝一杯82年的奶茶压压惊"],
                ['title' => "我是自愿上班的"],
                ['title' => "唉！又下班了，我还想工作"],
                ['title' => "心疼的抱住胖胖的自己"],
                ['title' => "无fxxk可说"],
                ['title' => "1个亿的小目标，加油"],
                ['title' => "Sorry，有钱就是能为所欲为"],
                ['title' => "和喜欢的一切在一起"],
                ['title' => "撸起袖子加油干"],
                ['title' => "除了我自己，谁还能打败我"],
                ['title' => "每天进步1%"],
                ['title' => "今天离梦想又远了一步"],
                ['title' => "每日三省吾身：高否帅否富否"],
                ['title' => "开机上班，开始混底薪"],
                ['title' => "关机下班，底薪到手"],
                ['title' => "包养自己"],
                ['title' => "幸福是奋斗出来的"],
                ['title' => "沉迷工作 日渐消瘦"],
                ['title' => "今天不加班"],
                ['title' => "绩效拿满分"],
                ['title' => "告别拖延症"],
                ['title' => "不经风雨，怎见彩虹"],
                ['title' => "脚踏实地，仰望星空"],
                ['title' => "没有梦想，何必远方"],
                ['title' => "要么突破，要么消亡"],
                ['title' => "只有工作能给我快乐"],
            ]
        ];
        if ($type == '') {
            $list = array_merge($arr[1], $arr[2], $arr[3], $arr[4], $arr[5]);
            return array_slice($list, ($pages - 1) * 40, 40);
        } else {
            return array_slice($arr[$type], ($pages - 1) * 40, 40);
        }
    }

    public static function getFileMimeType($tempFileName) {
        return strtolower(substr($tempFileName, strrpos($tempFileName, '.') + 1));
    }

    public static function GetTestPicId(){
        return 969507;
    }
    public static function GetTestGifId(){
        return 1835036;
    }

    /**
     * 等比缩放jpg图片
     * @param $src
     * @param $width
     * @param $quality
     * @param $format
     */
    public static function jpgResize($src, $newFilePath, $width, $quality, $format = 'jpg')
    {
        $info = getimagesize($src);
        $height = ($width / $info[0]) * $info[1];

        $image_wp = imagecreatetruecolor($width, $height);
        $image_src = imagecreatefromjpeg($src);


        if ($newFilePath == '') {
            $newFilePath = $src;
        }

        switch ($format) {
            case 'webp':
                imagecopyresized($image_wp, $image_src, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
                imagedestroy($image_src);
                imagewebp($image_wp, $newFilePath, $quality);
                break;
            default:
                imagecopyresampled($image_wp, $image_src, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
                imagedestroy($image_src);
                imagejpeg($image_wp, $newFilePath, $quality);
        }


        return $height;
    }

    public static function jpg2webp($src, $newFilePath, $q = 100)
    {
        $img = new \Imagick($src);
        $img->setImageFormat("webp");
//        $img->setImageCompression(Imagick::COMPRESSION_JPEG);
        $img->setImageCompressionQuality($q);
        $img->writeImage($newFilePath);
    }

    /**
     * 等比缩放jpg图片
     * @param $src
     * @param $width
     * @param $quality
     * @param $format
     */
    public static function jpgResize2($src, $newFilePath, $width, $format = 'jpg', $q = 100)
    {
        $img = new Imagick($src);

        $w = $img->getImageWidth();
        $h = $img->getImageHeight();
        $height = ($width / $w) * $h;

        if ($format == 'webp') {
            $img->setImageFormat("webp");
        }

        if ($q < 100) {
            $img->setImageCompressionQuality($q);
        }

        $img->resizeImage($width, $height, Imagick::FILTER_UNDEFINED, 1, 0);

        if ($newFilePath == '') {
            $newFilePath = $src;
        }
        $img->writeImage($newFilePath);

        return $height;
    }
    /**
     * 下载远程文件保存到本地
     * @param $url    远程文件链接
     * @param $save_dir 本地存储路径 默认存储在当前路径
     * @param $filename 图片存储到本地的文件名 不带后缀
     * @return string
     */
    public static function downFile($url,$save_dir='',$filename='')
    {
        if(trim($save_dir)==''){
            $save_dir = $_SERVER['DOCUMENT_ROOT'].'/video/png';
        }
        if (!is_dir($save_dir) || !is_writable($save_dir)) {
            \yii\helpers\FileHelper::createDirectory($save_dir, 0777, true);
        }
        $ext=strrchr($url,'.');
        //去除参数（auth_key）
        if(strstr($ext, '?')){
            $ext_arr = explode('?',$ext);
            $ext = $ext_arr[0];
        }
        if(trim($filename)==''){//保存文件名
            $filename=uniqid();
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $rawdata = curl_exec ($ch);
        curl_close ($ch);
        if(!$rawdata) return false;

        // 使用中文文件名需要转码
        $fp = fopen($save_dir.'/'.$filename.$ext,'w');
        fwrite($fp, $rawdata);
        fclose($fp);
        return $save_dir.'/'.$filename.$ext;
    }

    /**
     * 下载远程文件保存到本地
     * @param $url    远程文件链接
     * @param $save_dir 本地存储路径 默认存储在当前路径
     * @param $filename 图片存储到本地的文件名 不带后缀
     * @return string
     */
    public static function downloadRemoteFile($url,$save_dir='',$filename='')
    {
        if(trim($save_dir)==''){
            $save_dir = $_SERVER['DOCUMENT_ROOT'].'/video/png';
        }
        if (!is_dir($save_dir) || !is_writable($save_dir)) {
            \yii\helpers\FileHelper::createDirectory($save_dir, 0777, true);
        }
        $ext=strrchr($url,'.');
        //去除参数（auth_key）
        if(strstr($ext, '?')){
            $ext_arr = explode('?',$ext);
            $ext = $ext_arr[0];
        }
        if(trim($filename)==''){//保存文件名
            $filename=uniqid();
        }

        $filePath = $save_dir.'/'.$filename.$ext;

        $isSuccess = true;
        $fp = fopen($filePath,'w');
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 40); // 40s的时间限制

        curl_exec( $ch ); // 执行的结果集
        if ( curl_errno( $ch ) ) {
            $isSuccess = false;
        }

        curl_close( $ch );

        fclose( $fp );

        if ( !$isSuccess ) {
            @unlink($filePath);
        }

        return $filePath;
    }

    /**
     * zip解压方法
     * @param string $filePath 压缩包所在地址 【绝对文件地址】d:/test/123.zip
     * @param string $path 解压路径 【绝对文件目录路径】d:/test
     * @return bool
     */
    public static function unzip($filePath, $path) {
        if (empty($path) || empty($filePath)) {
            return false;
        }
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            $zip->extractTo($path);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }
    //删除指定文件夹以及文件夹下的所有文件
    public static function deldir($dir){
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    self::deldir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
    //删除指定文件
    public static function delFile($path){
        $url=iconv('utf-8','gbk',$path);
        if(PATH_SEPARATOR == ':'){ //linux
            unlink($path);
        }else{  //Windows
            unlink($url);
        }
    }

    /**
     * 获取文件夹内的所有文件名
     * @param $path
     * @return array
     */
    public static function getDir($path){
        $handler = opendir($path);
        $filename_all = [];
        while( ($filename = readdir($handler)) !== false )
        {
            //目录下都会有两个文件，名字为’.'和‘..’，不要对他们进行操作
            if($filename != "." && $filename != "..")
            {
                //进行处理
                $filename_all[] = $filename;
            }
        }
        //关闭目录
        closedir($handler);
        return $filename_all;
    }
    /**
     * 下载远程图片保存到本地
     * @param $imgUrl
     * @param string $saveDir 本地存储路径 默认存储在当前路径
     * @param null $fileName 图片存储到本地的文件名
     */
    public static function downloadImage($url, $save_dir='', $filename='', $type=0)
    {
        if (!is_dir($save_dir) || !is_writable($save_dir)) {
            \yii\helpers\FileHelper::createDirectory($save_dir, 0777, true);
        }

        if(trim($url)==''){
            return array('file_name'=>'','save_path'=>'','error'=>1);
        }
        if(trim($save_dir)==''){
            $save_dir='./';
        }
        if(trim($filename)==''){//保存文件名
            $ext=strrchr($url,'.');
            if (!in_array($ext, ['.gif', '.jpg', '.png'])) {
                return array('file_name'=>'','save_path'=>'','error'=>3);
            }
            $filename=uniqid().$ext;
        }

        //创建保存目录
        if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
            return array('file_name'=>'','save_path'=>'','error'=>5);
        }
        //获取远程文件所采用的方法
        if($type){
            $ch=curl_init();
            $timeout=30;
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            $img=curl_exec($ch);
            curl_close($ch);
        }else{
            ob_start();
            readfile($url);
            $img=ob_get_contents();
            ob_end_clean();
        }
        //$size=strlen($img);
        //文件大小
        $fp2=@fopen($save_dir.'/'.$filename,'a');
        $res = fwrite($fp2,$img);

        fclose($fp2);

        if (!$res) {
            unlink($save_dir.'/'.$filename);
            return ['error' => 4];
        }

        unset($img,$url);
        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
    }

    /**
     * 上传图片到cdn
     * @param $localFilePath
     * @param $cdnKey
     * @return bool
     * @throws OssException
     */
    public static function uploadToCdn($localFilePath, $cdnKey)
    {
        require_once dirname(__DIR__) . '/components/qiniu-sdk/autoload.php';

        $ossClient = new OssClient(Yii::$app->params['aliyun_pic_id'], Yii::$app->params['aliyun_pic_pass'], Yii::$app->params['aliyun_pic_endpoint']);
        $ossClient->setTimeout(300);
        $ossClient->setConnectTimeout(10);
        $res = $ossClient->uploadFile(Yii::$app->params['aliyun_pic_bucket'], $cdnKey, $localFilePath);

        // 暂时去掉  aliyun_preview_bucket 文件复制 詹中原 2021年5月11日15:41:20
        # $ossClient->copyObject(Yii::$app->params['aliyun_pic_bucket'], $cdnKey, Yii::$app->params['aliyun_preview_bucket'], $cdnKey);
        return true;
    }

    /**
     * 删除cdn图片
     * @param $cdnKey
     * @throws OssException
     */
    public static function delCdnFile($cdnKey,$bucket='pic')
    {
        if($bucket == 'js' || $bucket == 'pic'){
            $id = Yii::$app->params['aliyun_pic_id'];
            $pass = Yii::$app->params['aliyun_pic_pass'];
            $bucket_name = $bucket == 'js' ? Yii::$app->params['aliyun_js_bucket'] : Yii::$app->params['aliyun_pic_bucket'];
        }else{
            $id = Yii::$app->params['aliyun_pic_id_new'];
            $pass = Yii::$app->params['aliyun_pic_pass_new'];
            $bucket_name = $bucket == 'download' ? "tuguaishou-download" : "tuguaishou-userh5";
        }
        $ossClient = new OssClient($id, $pass, Yii::$app->params['aliyun_pic_endpoint']);
        $ossClient->setTimeout(120 /* seconds */);
        $ossClient->setConnectTimeout(10 /* seconds */);
        $ossClient->deleteObject($bucket_name, $cdnKey);
    }

    public static function getOssImgUrl($imgPath)
    {
        $nowTime = time() + 86400 * 30;
        $param = "";
        $path_parts = pathinfo($imgPath);
        $path_parts['basename'] = explode('!', $path_parts['basename']);
        $path_parts['basename'][0] = urlencode($path_parts['basename'][0]);
        $path_parts['basename'] = $path_parts['basename'][1] ? $path_parts['basename'][0] . "!" . $path_parts['basename'][1] : $path_parts['basename'][0];
        $return = "https:" . Yii::getAlias('@imgcdn') . $imgPath . "?auth_key={$nowTime}-0-0-" . md5("{$path_parts['dirname']}/{$path_parts['basename']}-{$nowTime}-0-0-" . Yii::$app->params['cdn_md5_salt']) . $param;
        return $return;
    }

    public static function isOldUser() {
        static $status;
        if ($status) return $status;

        $user_id = Yii::$app->user->id;
        if (!$user_id) {
            $status = "no";
        } else {
            $created = IpsUserInfo::get('created', $user_id);
            $days = date_diff(date_create(date('Y-m-d H:i:s')), date_create($created))->days;
            if ($days > 1) {
                $status = "yes";
            } else {
                $status = "no";
            }
        }
        return $status;
    }

    public static function differentUserPayLink($origin) {
        if (Tools::isOldUser() == "yes") {
            return "/dash/pay?classify=1&pay=36&origin={$origin}Pay";
        }
        return "/dash/vip-spec?classify=1&origin={$origin}Spec";
    }

    public static function keywordWithAlbumId($keyword, $class_id) {
        if ($keyword) return false;

        $class_list = [
            31 => [48242, 48235, 48228, 32729],
            10 => [48242, 48235, 48228, 48218],
            34 => [48235, 48228, 48242, 48218],
            33 => [48242, 48235, 48228, 48221],
            143 => [48228, 48242, 48235, 48218],
            106 => [48228, 48242, 48235, 48218],
            119 => [48228, 48242, 48235, 48218],
            278 => [48242, 48235, 48228, 48221],
        ];
        $class_list = [];
        $class_id = explode('_', $class_id);
        if (!$class_id[1] && !$class_id[2]) {
            $classWithAlbumId = $class_list[$class_id[0]];
        }

        if ($classWithAlbumId) {
            return $classWithAlbumId;
        }
        return false;
    }

    //用户用户的操作系统
    public static function getUserOS() {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $OS = 'ios';
        } elseif (strpos($agent, 'android')) {
            $OS = 'android';
        } elseif (preg_match('/win/i', $agent)) {
            $OS = 'WIN';
        } elseif (preg_match('/mac/i', $agent)) {
            $OS = 'MAC';
        } elseif (preg_match('/linux/i', $agent)) {
            $OS = 'Linux';
        } else {
            $OS = 'Other';
        }
        return $OS;
    }

    //获取用户手机品牌
    public static function getUserOSBand() {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $OS = 'ios';
        } elseif (strpos($agent, 'android')) {
            if (stripos($agent, "HUAWEI")!==false) {
                $OS = 'Huawei';
            }else if (strpos($agent, "MI")!==false || strpos($agent, "HM")) {
                $OS = 'MI';
            }else if (stripos($agent, "vivo")!==false) {
                $OS = 'vivo';
            } else {
                $OS = 'android';
            }
        } elseif (preg_match('/win/i', $agent)) {
            $OS = 'WIN';
        } elseif (preg_match('/mac/i', $agent)) {
            $OS = 'MAC';
        } elseif (preg_match('/linux/i', $agent)) {
            $OS = 'Linux';
        } else {
            $OS = 'Other';
        }
        return $OS;
    }

    //处理referer
    public static function dealReferer()
    {
        //包含区分route参数。则处理referer
        $route_area = Yii::$app->request->get('route_area');
        if($route_area!='avatarLeft') {
            $filter = ($route_area && explode('?',$_SERVER['HTTP_REFERER'])[0]=='https://818ps.com/');
        }else {
            $filter = $route_area;
        }
        if($filter) {
            $route_param = explode('&',$_SERVER['HTTP_REFERER']);
            foreach ($route_param as $k=>&$v) {
                $ex = explode('=',$v);
                if($ex[0]=='route') {
                    $sp = explode(',',$v);
                    if($sp[count($sp)-1]!='') {
                        $v = $v.'-'.$route_area;
                    }else {
                        $sp[count($sp)-2] = $sp[count($sp)-2].'-'.$route_area;
                        $v = implode(',',$sp);
                    }
                }elseif ($ex[0]=='after_route') {
                    $v = $v.'-'.$route_area;
                }
            }
            $http_referer = implode('&',$route_param);
            $_SERVER['HTTP_REFERER'] = $http_referer;
        }
    }

    public static function WhichEditor() {
        $referer = $_SERVER['HTTP_REFERER'];
        $referer = str_replace(['https://', 'http://'], ['', ''], $referer);
        $list = explode('.818ps.com', $referer);
        switch ($list[0]) {
            case "gif":
                return 'gif';
            case "ue":
                return 'pic';
            case "fwb":
                return 'fwb';
            default:
                return false;
        }
    }

    // textChange(wItemText = ""){
    //     wItemText = wItemText ? wItemText : "";
    //     if( wItemText == " " ){
    //         wItemText = "&nbsp;";
    //     }else{
    //         let lengthNum = wItemText.length;
    //         wItemText = "";
    //         for(let i = 0; i < lengthNum; i++){
    //             if( (i + 1) == lengthNum ){
    //                 wItemText += "&nbsp;";
    //             }else{
    //                 if( i % 2 ){
    //                     wItemText += " ";
    //                 }else{
    //                     wItemText += "&nbsp;";
    //                 }
    //             }

    //         }
    //     }
    //     return wItemText;
    // }

    public static function TextNbspHandle($t) {
        $t = preg_replace("/&nbsp;/", ' ', $t);
        preg_match("/^( )+/", $t, $tText);
        if( strlen($tText[0]) ){
            if( strlen($tText[0]) == 1 && $tText[0] === " " ){
                $tText = "&nbsp;";
            }else{
                $lengthNum = strlen($tText[0]);
                $tText = "";
                for($i = 0; $i < $lengthNum; $i++){
                    if( $i + 1 == $lengthNum ){
                        $tText .= "&nbsp;";
                    }else{
                        if( $i % 2 ){
                            $tText .= " ";
                        }else{
                            $tText .= "&nbsp;";
                        }
                    }
                }
            }
        }else{
            $tText = "";
        }

        preg_match("/( )+$/", $t, $wText);
        if( trim($t) == "" ){
            $wText = "&nbsp;";
        }else{
            if( strlen($wText[0]) ){
                if( strlen($wText[0]) == 1 && $wText[0] === " " ){
                    $wText = "&nbsp;";
                }else{
                    $lengthNum = strlen($wText[0]);
                    $wText = "";
                    for($i = 0; $i < $lengthNum; $i++){
                        if( $i + 1 == $lengthNum ){
                            $wText .= "&nbsp;";
                        }else{
                            if( $i % 2 ){
                                $wText .= " ";
                            }else{
                                $wText .= "&nbsp;";
                            }
                        }
                    }
                }
            }else{
                $wText = "";
            }
        }

        $t = trim($t);
        $t = preg_replace("/(  )/", ' &nbsp;', $t);
        return $tText . $t . $wText;

        // $t = str_split($t);
        // $count = count($t);
        // foreach ($t as $k => $v) {
        //     if ($k == ($count - 1)) {
        //         if ($t[$k] == " "){
        //             $t[$k] = "&nbsp;";
        //         }
        //     } elseif ($t[$k] == " " && $t[$k + 1] != " ") {
        //         $t[$k] = "&nbsp;";
        //     } elseif ($t[$k] == " " && $t[$k + 1] == " ") {
        //         $t[$k + 1] = "&nbsp;";
        //     }
        // }

        // return implode('', $t);
    }

    public static function push2Cmq($id, $add = 0, $template_type = 1)
    {
        if (!$id) {
            return false;
        }
        if($template_type == -10) { //吐槽大会队列
            $redis_key = 'RoastUserPreviewQueue';
        }elseif ($template_type == 2) {
            $redis_key = 'userPreviewQueueGif';
        } else {
            $redis_key = 'userPreviewQueue';
        }
        Yii::$app->redis6->zadd($redis_key, time() + $add, $id);

        //        $cookies_key_name='preview_' . $id;
        //        if ($_COOKIE[$cookies_key_name] && $_COOKIE[$cookies_key_name] > (time() - 120)) {
        //            return false;
        //        }
        //        try {
        //            $canPush = Yii::$app->redis5->sadd('preview', $id);
        //        } catch (\exception $e) {
        //            $canPush = true;
        //        }
        //
        //        if ($canPush == true) {
        //            AliYunQueue::pushIpsRender('user_templ_id=' . $id);
        //            setcookie($cookies_key_name, time(), time() + 120);
        //        }

        return true;
    }

    public static function isSearchPage() {
        $controller_id = Yii::$app->controller->id;
        $action_id = Yii::$app->controller->action->id;
        $page_info_id = $controller_id . '_' . $action_id;
        $params = Yii::$app->request->queryParams;

        if (in_array($page_info_id, ['site_search', 'site_color-search', 'site_baidu-search', 'site_detail'])) {
            return true;
        } else {
            return false;
        }
    }

    public static function witchEditorDomain($template_type) {
        switch ($template_type) {
            case 2:
                $domain = 'gif.818ps.com';
                break;
            case 3:
                $domain = 'ppt.818ps.com';
                break;
            case 4:
                $domain = 'movie.818ps.com';
                break;
            case 5:
                $domain = 'editorh5.818ps.com';
                break;
            case 6:
                $domain = 'fwb.818ps.com';
                break;
            case 7:
                $domain = 'editorh5l.818ps.com';
                break;
            default:
                $domain = 'ue.818ps.com';
                break;
        }
        return $domain;
    }

    public static function witchEditorDomain2($template_type,$kid_1) {
        switch ($template_type) {
            case 2:
                $domain = 'gif.818ps.com';
                break;
            case 3:
                $domain = 'ppt.818ps.com';
                break;
            case 4:
            case 9:
                $domain = 'movie.818ps.com';
                break;
            case 5:
                $domain = 'editorh5.818ps.com';
                break;
            case 6:
                $domain = 'fwb.818ps.com';
                break;
            case 7:
                $domain = 'editorh5l.818ps.com';
                break;
            default:
                if (in_array($kid_1, [3])) {
                    $domain = 'ecommerce.818ps.com';
                }else{
                    $domain = 'ue.818ps.com';
                }
                break;
        }
        return $domain;
    }

    /**
     * 获取链接 详情
     * @param $url
     * @return array|bool
     */
    public static function getUrlInfo($url)
    {
        if(!$arr = parse_url($url)) return false;
        parse_str($arr['query'], $output);
        $url_arr = [
            'host'=>$arr['host'],
            'query'=>$output,
        ];
        return $url_arr;
    }

    //SEO主动推送
    public static function pushSeoUrl($urls) {
        $api = 'http://data.zz.baidu.com/urls?site=https://818ps.com&token=4SpAxmftgOMBoutL';
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        return curl_exec($ch);
    }

    //首页底部热门海报设计
    public static function hotHbLink()
    {
        $arr = [
            '宣传海报模板'=>'https://818ps.com/muban/xuanchuanhaibao.html',
            '朋友圈图片'=>'https://818ps.com/muban/b0b590aa77e32cb17a1fa25d140d9112.html',
            '端午节粽子图片'=>'https://818ps.com/muban/af0b2f9754adc30e3df81deaa853c560.html',
            '放假通知'=>'https://818ps.com/muban/fangjiatongzhi.html',
            '奖状模板'=>'https://818ps.com/muban/jiangzhuangmuban.html',
            '父亲节海报'=>'https://818ps.com/muban/fuqinjiehaibao.html',
            '同学聚会邀请函'=>'https://818ps.com/muban/tongxuejuhuiyaoqinghan.html',
            '淘宝主图设计'=>'https://818ps.com/muban/taobaozhutusheji.html',
            '菜谱模板'=>'https://818ps.com/muban/caipu.html',
            '会议邀请函模板'=>'https://818ps.com/muban/huiyiyaoqinghan.html',
            '电子小报模板'=>'https://818ps.com/muban/dianzixiaobao.html',
            '微信公众号推文模板'=>'https://818ps.com/muban/weixingongzhonghaotuiwen.html',
            '端午节海报'=>'https://818ps.com/muban/duanwujiehaibao.html',
            '招生海报模板'=>'https://818ps.com/muban/zhaoshenghaibaomuban.html',
            '早安励志图片'=>'https://818ps.com/muban/zaoanlizhi.html',
            '禁毒宣传海报'=>'https://818ps.com/muban/jinduxuanchuanhaibao.html',
            '手抄报模板'=>'https://818ps.com/muban/shouchaobaomuban.html',
            '公众号首图'=>'https://818ps.com/muban/gongzhonghaoshoutu.html',
            '聘书模板'=>'https://818ps.com/muban/pinshumuban.html',
            '开业宣传海报'=>'https://818ps.com/muban/kaiyexuanchuanhaibao.html'
        ];
        return $arr;
    }

    /*
     * 是否签到
     */
    public static function isSign() {
        $uid = Yii::$app->user->id;
        if (!$uid) {
            return ['isSign' => 0, 'continue' => 1];
        }
        $isSign = Tools::getRedis(6, 'sign_record:' . date('Y-m-d') . ':' . $uid);
        if (isset($isSign['sign']) && $isSign['sign'] == 1) {
            return ['isSign' => 1, 'continue' => $isSign['continue']];
        } elseif (isset($isSign['sign'])) {
            return ['isSign' => 0, 'continue' => $isSign['continue']];
        } else {
            $yes_isSign = Tools::getRedis(6, 'sign_record:' . date('Y-m-d', time() - 86400) . ':' . $uid);
            if ($yes_isSign) {
                return ['isSign' => 0, 'continue' => $yes_isSign['continue'] + 1];
            } else {
                return SigninRecord::getSignInfo($uid);
            }
        }
    }

    public static function isStartDesigner($action_id, $controller_id = "") {
        if ($controller_id == 'team') {
            return true;
        }
        return in_array($action_id, ['start-design', "my-fav", "my-vip", "my-integral", "downloaded", "account-bind", "featured-templ", "my-download","marketing","paper-box"]);
    }

    public static function isFirmPage($controller_id) {
        if ($controller_id == "firm") return true;
        return false;
    }

    /**
     * 上传oss
     * @param $filePath
     * @param $fileType
     * @param string $cdnname
     * @return string
     * @throws OssException
     */
    public static function saveOss($filePath, $fileType, $cdnname='')
    {
        $microTime = microtime(true);
        $time = strtotime(date('Y-m-d H:i:s', $microTime));
        $fileName = '-3' . '-' . $microTime . '-' . mt_rand(10000, 99999);
        $fileName = md5($fileName);
        $fileNamePathArr = str_split($time, 2);
        $fileNamePath = Yii::getAlias('@oss_dsr_lg') . "/{$fileNamePathArr[0]}/{$fileNamePathArr[1]}/{$fileNamePathArr[2]}/{$fileNamePathArr[3]}/{$fileNamePathArr[4]}/" . substr($fileName, 0, 2);
        $fileNameTrue = "{$fileNamePath}/{$fileName}.{$fileType}";
        if ($cdnname) {
            $fileNameTrue = $cdnname;
        }
        $ossClient = new OssClient(Yii::$app->params['aliyun_pic_id'], Yii::$app->params['aliyun_pic_pass'], Yii::$app->params['aliyun_pic_endpoint']);
        $ossClient->setTimeout(300);
        $ossClient->setConnectTimeout(10);
        $ossClient->uploadFile(Yii::$app->params['aliyun_pic_bucket'], $fileNameTrue, $filePath);

        return $fileNameTrue;
    }

    /**
     * 获取组合字翻新类型
     */
    public static function getGroupFixKind() {
        return GroupFixKind::getKind();
    }

    public static function pushContent2oss($fileName, $content, $bucket = 'img') {
        $fileName = ltrim($fileName, "/");
        try {

            if($bucket == 'js' || $bucket == 'pic' || $bucket == 'img'){
                $id = Yii::$app->params['aliyun_pic_id'];
                $pass = Yii::$app->params['aliyun_pic_pass'];
                $bucket_name = $bucket == 'js' ? Yii::$app->params['aliyun_js_bucket'] : Yii::$app->params['aliyun_pic_bucket'];
            }else{
                $id = Yii::$app->params['aliyun_pic_id_new'];
                $pass = Yii::$app->params['aliyun_pic_pass_new'];
                $bucket_name = $bucket == 'download' ? "tuguaishou-download" : "tuguaishou-userh5";
            }
            $ossClient = new OssClient($id, $pass, Yii::$app->params['aliyun_pic_endpoint']);
//            $ossClient = new OssClient($id, $pass, Yii::$app->params['aliyun_pic_endpoint_out']);
            $ossClient->setTimeout(60 /* seconds */);
            $ossClient->setConnectTimeout(10 /* seconds */);
            $ossClient->putObject($bucket_name, $fileName, $content);
        } catch (OssException $e) {
            echo $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * 通过内网下载oss文件
     * @param $ossObjectName
     * @param $localFile
     * @throws \OSS\Core\OssException
     */
    public static function downloadInternalOss($ossObjectName, $localFile) {
        $options = [
            OssClient::OSS_FILE_DOWNLOAD => $localFile
        ];
        $ossClient = new OssClient(Yii::$app->params['aliyun_pic_id'], Yii::$app->params['aliyun_pic_pass'], 'oss-cn-shanghai-internal.aliyuncs.com');
        //1127_psd
//        $ossClient = new OssClient(\Yii::$app->params['aliyun_pic_id'], \Yii::$app->params['aliyun_pic_pass'], \Yii::$app->params['aliyun_pic_endpoint_out_str']);
        $ossClient->getObject(\Yii::$app->params['aliyun_pic_bucket'], ltrim($ossObjectName, '\/'), $options);
    }

    public static function getNonceStr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public static function udesk() {
        $p['nonce'] = self::getNonceStr();
        $p['timestamp'] = time() . rand(100, 999);
        $p['web_token'] = Yii::$app->user->id;
        $sign_str = "";
        $im_user_key = '256ec1bc8e7b9011166ac2563f7fa7fc';
        foreach ($p as $k => $v) {
            $sign_str .= $k . "=" . $v . "&";
        }
        $sha1 = strtoupper(sha1($sign_str . $im_user_key));

        $sign_str .= "c_name=" . $p['web_token'] . "&";
        $sign_str .= "c_cn_ip=" . self::getUserIp() . "&";
        if ($p['web_token']) {
            $vip = IpsUserInfo::get('vip', $p['web_token']);
            $sign_str .= "c_vip=" . $vip > 1 ? 'vip' : 'normal' . "&";
            $sign_str .= "c_desc=" . $vip . "&";
        }
        return $sign_str . "signature=" . $sha1;
    }

    public static function checkTextSafe($xcxType,$content,$refresh =0)
    {
        if(!in_array($xcxType,['gif','pic'])) return true;

        $accessToken = XiaoChengXu::accessToken($refresh,$xcxType);
//        $data["content"] = $content;

        $url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token=".$accessToken;
        $res = self::http_post($url, '{"content":"'.$content.'"}');
        $res = json_decode($res, 1);

        if(!in_array($res['errcode'],[87014,40001])) {
            return true;
        }elseif ($res['errcode']==40001 && !$refresh) {
            self::checkTextSafe($xcxType,$content,1);
        }else {
            return false;
        }

    }


    public static function uploadSafe($xcxType,$filePath,$refresh =0)
    {
        if(!in_array($xcxType,['gif','pic'])) return true;

        $accessToken = XiaoChengXu::accessToken($refresh,$xcxType);

        //创建临时文件
        $fileType = explode('.',$filePath)[1];
        $filePath = "https:".Yii::getAlias('@imgcdn').ImageUrlPro::getSaltImgUrl($filePath . '!l280');

        $localPath = Yii::$app->basePath.'/web/upload/safe/';
        $tmpName = time().rand(1,999999).'.'.$fileType;
        if(!is_dir($localPath)) {
            mkdir($localPath);
        }
        $fileData = file_get_contents($filePath);
        file_put_contents($localPath.$tmpName,$fileData);

        $file_path = $localPath.$tmpName;

        $url = "https://api.weixin.qq.com/wxa/img_sec_check?access_token=".$accessToken;
        $res = json_decode(self::wx_post_file($url,$file_path),1);
        IpsLog::info('upload_safe_back',$res['errcode'].','.$filePath);
        unlink($file_path);
        if(!in_array($res['errcode'],[87014,40001])) {
            return true;
        }elseif ($res['errcode']==40001 && !$refresh) {
            self::uploadSafe($xcxType,$filePath,1);
        }else {
            return false;
        }
    }

    public static function wx_post_file($url,$path){
        $data = array(
            'media'   =>  new \CURLFile($path),
            'name' => 'file'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  //该curl_setopt可以向header写键值对
        curl_setopt($ch, CURLOPT_HEADER, 'Content-type:multipart/form-data');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function isPc()
    {
        return Yii::$app->request->get('isClientSide')==1;
    }

    public static function getUserBase($new='')
    {
        $userId = intval(Yii::$app->user->id);
        $created = IpsUserInfo::get('created', $userId);
        $vip = IpsUserInfo::get('vip', $userId);
        $vip_pic = IpsUserInfo::get('vip_pic', $userId);
        $vip_gif = IpsUserInfo::get('vip_gif', $userId);
        $vip = max($vip, $vip_pic, $vip_gif);
        $user_reg_source = IpsUserInfo::get('user_reg_source');
        $user_source = Tools::getUserSource();
        $track_id = Tools::track_id();
        $gray_release = GRAY_RELEASE;
        $response = base64_encode("uid={$userId}&created={$created}&vip={$vip}&user_source={$user_source}&track_id={$track_id}&gray={$gray_release}&user_reg_source={$user_reg_source}");
        $response_new = base64_encode("uid={$userId}&uc={$created}&v={$vip}&us={$user_source}&t={$track_id}&gr={$gray_release}&urs={$user_reg_source}");
        if ($new) {
            $response = $response_new;
        }
        if ($_COOKIE['ui_818ps'] != $response_new) {
            setcookie("ui_818ps", $response_new, time() + 86400 * 7, '/', '.818ps.com');
        }
        return $response;
    }

    public static function getUserSource() {
        $source_list = ['qt', 'qt1', 'qt2', 'qk', 'yj', 'st', 'qtg', 'qg', '90sj'];
        $user_source = Yii::$app->request->get('user_source');

        $first = substr($user_source, 0, 1);
        if ($first == 'r') {
            $num = explode('r', $user_source)[1];
            $num = (int)($num);
            $user_source = 'r' . $num;
        }
        if ((Yii::$app->getRequest()->getPathInfo() == 'client/login' && (!$user_source || $user_source == 'default')) || (Tools::isPc() && !(Yii::$app->session->get('user_source')))) {
            //客户端特殊us
            $user_source = 'r318237';
        }

        if ($user_source && (in_array($user_source, $source_list) || strstr($user_source, 'qt') || strstr($user_source, 'r'))) {
            if ($user_source != 'r644' || ($user_source == 'r644' && !$_SERVER['HTTP_REFERER'])) {
                Yii::$app->session->set('user_source', $user_source);
            }
        }
        $user_source = Yii::$app->session->get('user_source');
        return $user_source;
    }

    /**
     * 返回用户平台
     */
    public static function getUserPlat()
    {
        $platform = 'web';
        if(Tools::isPc() || Yii::$app->session->get('user_source')=='r318237') {
            $platform = 'pc';
        }
        if(Tools::isWap()){
            $platform = 'm';
        }
        return $platform;
    }

    public static function curlGet($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            print_r(curl_error($curl)).PHP_EOL;
        }
        curl_close($curl);
        return $data;
    }

    public static function allowOrigin()
    {
        return [
            'https://ecommerce.818ps.com:3000',
            'https://ecommerce.818ps.com',
            'http://ecommerce.818ps.com:3000',
            'http://ecommerce.818ps.com',

            'https://editorh5.818ps.com:3000',
            'https://editorh5.818ps.com',
            'http://editorh5.818ps.com:3000',
            'http://editorh5.818ps.com',

            'https://editorh5l.818ps.com:3000',
            'https://editorh5l.818ps.com',
            'http://editorh5l.818ps.com:3000',
            'http://editorh5l.818ps.com',

            'https://movie.818ps.com:3000',
            'https://movie.818ps.com',
            'http://movie.818ps.com:3000',
            'http://movie.818ps.com',

            'https://pptrender.818ps.com:3000',
            'https://pptrender.818ps.com',
            'http://pptrender.818ps.com:3000',
            'http://pptrender.818ps.com',

            'https://ppt.818ps.com:3000',
            'https://ppt.818ps.com',
            'http://ppt.818ps.com:3000',
            'http://ppt.818ps.com',

            'https://gif.818ps.com:3000',
            'https://gif.818ps.com',
            'http://gif.818ps.com:3000',
            'http://gif.818ps.com',

            'https://ue.818ps.com:3000',
            'https://ue.818ps.com',
            'http://ue.818ps.com:3000',
            'http://ue.818ps.com',

            'https://h5.818ps.com',
            'https://h5-test.818ps.com',
            'http://h5.818ps.com',
            'http://h5-test.818ps.com',

            'https://818ps.com',
            'http://818ps.com',

            'https://h5-test.818ps.com:3000',
            'https://h5-test.818ps.com',
            'http://h5-test.818ps.com:3000',
            'http://h5-test.818ps.com',

            'https://h5.818ps.com:3000',
            'https://h5.818ps.com',
            'http://h5.818ps.com:3000',
            'http://h5.818ps.com',

            'https://pc-electron.818ps.com:3000',
            'https://pc-electron.818ps.com',
            'http://pc-electron.818ps.com:3000',
            'http://pc-electron.818ps.com',

            'http://portal.818ps.com',
            'https://portal.818ps.com',

            'https://m.818ps.com',
            'http://m.818ps.com',

            'https://588ku.tuguaishou.com',
            'http://588ku.tuguaishou.com',


            'https://t1.818ps.com:3000',
            'https://t1.818ps.com',
            'http://t1.818ps.com:3000',
            'http://t1.818ps.com',

            'https://electroncontent.818ps.com:3000',
            'https://electroncontent.818ps.com',
            'http://electroncontent.818ps.com:3000',
            'http://electroncontent.818ps.com',

            'https://fwb.818ps.com:3000',
            'https://fwb.818ps.com',
            'http://fwb.818ps.com:3000',
            'http://fwb.818ps.com',

            'https://ecommerce.818ps.com:3000',
            'https://ecommerce.818ps.com',
            'http://ecommerce.818ps.com:3000',
            'http://ecommerce.818ps.com',

            'https://uh5.818ps.com',
            'http://uh5.818ps.com',

            'https://sh5.818ps.com',
            'http://sh5.818ps.com',

            'https://ah5.818ps.com',
            'http://ah5.818ps.com',

            'https://bh5.818ps.com',
            'http://bh5.818ps.com',

            'https://ch5.818ps.com',
            'http://ch5.818ps.com',

        ];
    }

    public static function millisecond2min($millisecond) {
        $min = "00";
        $second = intval($millisecond / 1000);
        if ($second >= 60) {
            $min = intval($second / 60);
            $second = intval($second / 60);
        }

        $min = str_pad($min, 2, 0, STR_PAD_LEFT);
        $second = str_pad($second, 2, 0, STR_PAD_LEFT);
        return $min . ":" . $second;
    }

    /**
     * @param string $method
     * @return bool
     * 判断是否7折抵扣
     */
    public static function isFans($method = 'get')
    {
        //每次更换act需要清除上一次的redis
        $origin = $method=='post'?$_POST['pay_origin']:$_GET['origin'];
        if(substr($origin,0,5)=='fans_') {
            $act = substr($origin,5,1);
            if($act && Tools::getRedis(6,'isFans'.$act)>0 && time()>=strtotime('2019-12-04') && time()<=strtotime('2019-12-09 23:59:59')) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检验客户端版本
     * @param $now_version
     * @param $support
     * @return bool
     */
    public static function checkPcVersion($now_version,$support)
    {
        if(!$now_version) {
            return false;
        }
        $now_version = explode('.',$now_version);
        $support = explode('.',$support);
        foreach ($now_version as $k=>$v) {
            if((int)$v<(int)$support[$k]) {
                return false;
            } elseif ((int)$v>(int)$support[$k]) {
                return true;
            } else {
                continue;
            }
        }
        return true;

    }

    /**
     * 导出excel
     */

    public static function exportExcel($headArr,$data,$fileName)
    {
        include('../components/PhpExcelClasses/PHPExcel.php');
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头 超过26列
        $key = 0;
        foreach($headArr as $v){
            //注意，不能少了。将列数字转换为字母\
            $colum = \PHPExcel_Cell::stringFromColumnIndex($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }
        //设置表头
        $column = 2; //从第二行写入数据 第一行是表头
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach($data as $key => $rows) { //行写入
            $span = 0;
            foreach ($rows as $keyName => $value) {// 列写入
                $j = \PHPExcel_Cell::stringFromColumnIndex($span);
                $value = self::removeEmoji($value);
                $objActSheet->setCellValueExplicit($j . $column, $value, \PHPExcel_Cell_DataType::TYPE_STRING);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$fileName.xls");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }

    function removeEmoji($text){
        $len = mb_strlen($text);
        $newText = '';
        for($i=0;$i<$len;$i++){
            $str = mb_substr($text, $i, 1, 'utf-8');
            if(strlen($str) >= 4) continue;//emoji表情为4个字节
            $newText .= $str;
        }
        return $newText;
    }

    /**
     * 检验文件在OSS上面是否存在
     * @param $bucket
     * @param $object
     * @return bool
     */
    public static function checkOssObjectExist($object,$bucket='js')
    {
        try {
            if($bucket == 'js' || $bucket == 'pic'){
                $id = Yii::$app->params['aliyun_pic_id'];
                $pass = Yii::$app->params['aliyun_pic_pass'];
                $bucket_name = $bucket == 'js' ? Yii::$app->params['aliyun_js_bucket'] : Yii::$app->params['aliyun_pic_bucket'];
            }else{
                $id = Yii::$app->params['aliyun_pic_id_new'];
                $pass = Yii::$app->params['aliyun_pic_pass_new'];
                $bucket_name = $bucket == 'download' ? "tuguaishou-download" : "tuguaishou-userh5";
            }
            $ossClient = new OssClient($id, $pass, Yii::$app->params['aliyun_pic_endpoint']);
            return $ossClient->doesObjectExist($bucket_name, ltrim($object, '/'));
        }catch (OssException $e) {
            return false;
        }

    }

    /**
     * 获取富文本HTML
     */
    public static function getRtHtml($html,$scale=1,$designer=0)
    {
        if(true) {
            $style = 'margin: 0;padding: 0;transform-origin:0 0;transform:scale('.$scale.','.$scale.');width:500px';
            $html_before = "<!doctype html><html><head><style>"
                . "*{"
                . "box-sizing:border-box;"
//                . "-webkit-max-logical-width: 100%;"
                . "margin: 0;"
                . "padding: 0;"
                . "user-select: auto;"
                . "font-family: syst;"
                . "line-height: 1.6;"
                . "}"
                . "::-webkit-scrollbar{width: 0;}::-webkit-scrollbar-thumb {display:none;border-radius: 6px;-webkit-box-shadow: inset 0 0 5px rgba(0,0,0,0.2);background:rgba(233,233,233,1);}"
                ."a{text-decoration:none;}"
                ."table{margin-bottom:10px;border-collapse:collapse;display:table;width:100%;margin:0 auto;}"
                ."td,th{word-wrap:break-word;word-break:break-all;padding:5px;border:1px solid #ddd;}"
                ."body *{max-inline-size:100%;}"
                ."</style><meta name='viewport' content='width=500,initial-scale=1.0,maximum-scale=1.0,user-scalable=0'></head><body style='".$style."'>";
        }else {
            $style = 'font-family: "Helvetica Neue", Helvetica, "Hiragino Sans GB", "Apple Color Emoji", "Emoji Symbols Font", "Segoe UI Symbol", Arial, sans-serif, 宋体;margin: 0;padding: 0;transform-origin:0 0;transform:scale('.$scale.','.$scale.');width:500px';
            $html_before = "<!doctype html><html><head><style>"
                . "*{"
                . "box-sizing:border-box;"
                . "-webkit-max-logical-width: 100%;"
                . "margin: 0;"
                . "padding: 0;"
                . "user-select: auto;"
                . "line-height: normal;"
                . "}"
                . "::-webkit-scrollbar{width: 0;}::-webkit-scrollbar-thumb {display:none;border-radius: 6px;-webkit-box-shadow: inset 0 0 5px rgba(0,0,0,0.2);background:rgba(233,233,233,1);}</style><meta name='viewport' content='width=500,initial-scale=1.0,maximum-scale=1.0,user-scalable=0'></head><body style='".$style."'>";
        }
        $html_script = '<script>
    var metaList = document.getElementsByTagName("meta");
    console.log("document.body.offsetWidth: ", document.body.offsetWidth);
    var viewPortScale = window.screen.width / 500;
    console.log(viewPortScale);
    for(var i = 0; i < metaList.length; i++){
        var metaItem = metaList[i];
        console.log("metaItem: ", metaItem);
        if(metaItem.getAttribute("name") == "viewport"){

            metaItem.content = "width=340,initial-scale="+viewPortScale+",maximum-scale="+viewPortScale+",user-scalable=no";
        }
    }
</script>';
        $html_after = '</body>'.$html_script.'</html>';
        return $html_before.htmlspecialchars_decode(htmlspecialchars($html)).$html_after;
    }

    public static function week2chinese($week) {
        $week_map = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
        return $week_map[$week];
    }

    public static function pushDesignerQueue($tid,$template_type)
    {
        switch ($template_type) {
            case 2:
                //GIF
                AliYunQueue::pushIpsRender('templId=' . $tid, "ips-render-gif");
                break;
            case 3:
                //PPT
                AliYunQueue::pushIpsRender('templId=' . $tid, "ips-render-ppt");
                break;
            case 4:
                //视频
                $preview_frame = MovieFrame::findOne(['tid' => $tid])['frame'];
                AliYunQueue::pushIpsRender('templId=' . $tid . "=" . $preview_frame, "ips-movie-preview");
                break;
            case 6:
                //富文本
                AliYunQueue::pushIpsRender('templId=' . $tid, 'ips-render-rt');
                break;
            default:
                //静态图  H5  长页H5
                AliYunQueue::pushIpsRender('templId=' . $tid);
                break;
        }
    }

    /**
     * xss clean
     * @param $content
     * @return string
     */
    public static function xssClean($content) {
        if(empty($content)) {
            return $content;
        }

        return HtmlPurifier::process($content);
    }

    /**
     * 获取用户服务实现
     * @return UserCacheService
     */
    public static function getUserCacheService(): UserCacheService {
        return (Yii::$container->get(UserCacheService::class));
    }

    public static function getChineseSplit($search, $split = ' ') {
        if(empty($search)) {
            return $search;
        }

        $ret = '';
        $len = mb_strlen($search,'UTF-8');

        for($i = 0; $i < $len; $i++) {
            $str = mb_substr($search, $i, 1,'UTF-8');

            if( !empty($str) && preg_match('/[^\x00-\x80]/', $str)) {
                $str = $split.$str;
            }

            $ret .= $str;
        }

        return $ret;
    }

    /**
     * 上传文件至oss
     * @param $files //文件内容  $_FILES
     * @param $bucket // 上传的bucket
     * @param string[] $format 支持的格式
     * @param string $topPath 首层文件夹
     * @param string $path 自定义文件夹
     * @return array
     * @throws OssException
     * @author 詹中原
     * @date 2021/1/13
     * @time 15:21
     */
    public static function UploadFileToOss($files,$bucket,$format = ['jpg','jpeg','png'],$topPath = '',$path = ''){
        #获取上传文件格式
        $ext = substr($files['name'], strrpos($files['name'], '.') + 1);
        if(!in_array($ext,$format)){
            return ['state'=>false,'msg'=>'不支持的文件格式'];
        }
        $microTime = microtime(true);
        $y = date('Y', $microTime);
        $m = date('m', $microTime);
        $d = date('d', $microTime);
        $h = date('H', $microTime);
        //首层文件夹
        $defaultTopPath = 'default';
        if($topPath){
            $defaultTopPath = $topPath;
        }
        //子文件夹  /2021/01/13/15  年月日时
        $subFolders = "/{$y}/{$m}/{$d}/{$h}/";

        //完整文件夹 路径 ps：$bucket/$defaultTopPath/$subFolders
        $filePath = $defaultTopPath.$subFolders;
        //自定义文件夹
        if($path){
            //路径 ps：$bucket/$path
            $filePath = $path;
        }
        //生成文件名
        $rand = $microTime . '-' . mt_rand(10000, 99999);
        $fileName = md5($files['name'].$rand).'.'.$ext;
        //生成文件路径
        $filePathName = $filePath.$fileName;
        //链接oss
        $ossClient = new OssClient(Yii::$app->params['aliyun_pic_id'], Yii::$app->params['aliyun_pic_pass'], Yii::$app->params['aliyun_pic_endpoint']);
        $ossClient->setTimeout(300);
        $ossClient->setConnectTimeout(10);
        try {
            //上传文件
            $ossClient->uploadFile(Yii::$app->params[$bucket], $filePathName, $files['tmp_name']);
            //第一层目录   子目录    文件名   完整文件名
            $data = ['top_path'=>$defaultTopPath,'sub_folders'=>$subFolders,'file_name'=>$fileName,'file_path_name'=>$filePathName];
            return ['state'=>true,'msg'=>'上传文件成功','data'=>$data];
        } catch (OssException $e) {
            return ['state'=>false,'msg'=>'上传文件失败' . $e->getMessage()];
        }
    }

    public function http_post_data($url, $data_string)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string))
        );
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();

        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return  $return_content;
    }

    public function GetAccessTokenXcx(){
        $keyName = 'redisTokenXcx:';
        $at_info = Yii::$app->redis9->get($keyName);
        if ($at_info == '') {
            $request = Tools::curl('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx80f3d38f39b15458&secret=5e9522e065ebfa1da61c97a56e22d5c8');
            $request = json_decode($request, 1);
            if (!$request['access_token']) {
                IpsWarning::record('微信开发', '获取AccessToken失败 : ' . $request['errmsg']);
                return false;
            }
            $access_token = $request['access_token'];
            Yii::$app->redis9->set($keyName, $access_token);
            Yii::$app->redis9->expire($keyName, 3600);
        }

        return $at_info;
    }

//    post 提交数据
    public static function post_curl($url,$path){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $path);  //该curl_setopt可以向header写键值对
        curl_setopt($ch, CURLOPT_HEADER, 'Content-type:multipart/form-data');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        if (curl_errno($ch)) {
            print_r(curl_error($ch)).PHP_EOL;
        }
        return $output;
    }
    public static function filter($word){
        $origin = $word;
        // Filter 英文标点符号
        $word = preg_replace("/[[:punct:]]/i"," ",$word);

        // Filter 中文标点符号
        mb_regex_encoding('utf-8');
        $char = "，。、！？：；﹑•＂…‘’“”〝〞∕¦‖—　〈〉﹞﹝「」‹›〖〗】【»«』『〕〔》《﹐¸﹕︰﹔！¡？¿﹖﹌﹏﹋＇´ˊˋ―﹫︳︴¯＿￣﹢﹦﹤‐­˜﹟﹩﹠﹪﹡﹨﹍﹉﹎﹊ˇ︵︶︷︸︹︿﹀︺︽︾ˉ﹁﹂﹃﹄︻︼（）";
        $word = mb_ereg_replace("[".$char."]"," ",$word,"UTF-8");

        $ls = mb_strlen($word) - mb_strlen(ltrim($word));
        $ll = mb_strlen(trim($word));
        $ll = $ll < 50 ? $ll : 50;
        $word = mb_substr($origin, $ls, $ll);

        return $word;
    }

    public static function create_guid() {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = '';
        $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
        return $uuid;
    }

    /**
     * 基于base64的数据加密  压缩
     * @param $data
     * @return string
     * @author 詹中原
     * @date 2021/6/17
     * @time 17:23
     */
    public static function getEncryption($data){
        return base64_encode(gzcompress(serialize($data)));
    }

    /**
     * 解压缩 解密
     * @param $str
     * @return mixed
     * @author 詹中原
     * @date 2021/6/17
     * @time 17:24
     */
    public static function unEncryption($str){
        return unserialize(gzuncompress(base64_decode($str)));
    }

    public static function curlPro($url, $data = [], $method = 'GET', $header = [], $timeout = 600){
        $res = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            // 当需要通过curl_getinfo来获取发出请求的header信息时,该选项需要设置为true
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        switch (strtoupper($method)) {
            case 'GET':
                if (!empty($data)) {
                    $url = $url.'?'.http_build_query($data);
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
                break;
            case 'POST':
                if (class_exists('\CURLFile')) {
                    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
                }elseif (defined('CURLOPT_SAFE_UPLOAD')) {
                    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
        }
        $beginTime = microtime(true);
        $result = [
            'url'         => $url,
            'requestInfo' => curl_exec($ch),
            'execTime'    => microtime(true) - $beginTime,
        ];
        // curl报错
        if (curl_errno($ch)) {
            $result['error_code'] = curl_errno($ch);
            $result['error_msg']  = curl_error($ch);
        } else {
            $result['getInfo'] = curl_getinfo($ch);
        }
        // 关闭会话
        curl_close($ch);

        return $result;
    }

    /**
     * 是否回源
     * @param int $prep
     * @return bool
     */
    public static function isReturnSource($prep = 0)
    {
        // 12336807 龙雨洁
        $uids = [41, 2626047, 1582045, 4867228, 3837014, 2936030, 9667287];
//        2626047 朱天会
//        1582045 殷龙龙
//        4867228 何丽
//        3837014 丁胜男
//        9667287 储召琴
        //true 是控制台程序运行
        if (Yii::$app->id == 'basic-console') {
            $uid = 0;
        } else {
            $uid = Yii::$app->user->id;
        }
        if (($prep == 1 || $_GET['prep'] == 1) && in_array($uid, $uids)) {
            return true;
        }
        return false;
    }

    /**
     * 缓存是否回源
     * @param int $prep
     * @return bool
     */
    public static function isReturnSourceVisitor($prep = 0)
    {
        if (($prep == 1 || $_GET['prep'] == 1)) {
            return true;
        }
        return false;
    }

}
