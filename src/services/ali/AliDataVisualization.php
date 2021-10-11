<?php

namespace app\services\ali;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Sts\Sts;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Client\Exception\ClientException;

class AliDataVisualization
{
    public function __construct(
        private string $projectName,
        private string $logStoreName,
        private array $otherParams = []
    ) {
    }

    /**
     * get sign url
     */
    public function getSignInUrl()
    {
        try {
            // construct final url
            // Destination为最终嵌入的页面地址，构造说明参考：https://help.aliyun.com/document_detail/103028.html
            $slsUrl = (new AliSls4service())
                ->setProjectName($this->projectName)
                ->setLogStoreName($this->logStoreName)
                ->setOtherParams($this->otherParams)
                ->buildUrl();

            $signInUrl = getenv('ALIYUN_SIGN_IN_HOST')."/federation?Action=Login"
                ."&LoginUrl=".urlencode("https://www.aliyun.com")
                ."&Destination=".urlencode($slsUrl)
                ."&SigninToken=".urlencode($this->getSignInToken());

            return [
                'code' => 'get_sign_url',
                'message' => 'Get Sign Url',
                'url' => $signInUrl
            ];

        } catch (ServerException $e) {
            \Yii::error("Error: ".$e->getErrorCode()." Message: ".$e->getMessage(), __METHOD__);
            return [ 'code' => "server_error", 'message' => $e->getMessage() ];
        } catch (ClientException $e) {
            \Yii::error("Error: ".$e->getErrorCode()." Message: ".$e->getMessage(), __METHOD__);
            return [ 'code' => 'client_error', 'message' => $e->getMessage() ];
        }
    }

    /**
     * get sign token
     */
    public function getSignInToken()
    {
        // 只允许子用户使用角色
        AlibabaCloud::accessKeyClient(
            getenv('ALIYUN_ACCESS_KEY_ID'),
            getenv('ALIYUN_ACCESS_KEY_SECRET')
        )
            ->regionId("cn-shanghai")
            ->asDefaultClient();

        $roleArnSession = "slsconsole-session";

        $response = Sts::v20150401()
            ->assumeRole()
            //指定角色ARN
            ->withRoleArn(getenv('ALIYUN_ROLE_ARON'))
            //RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
            ->withRoleSessionName($roleArnSession)
            //设置权限策略以进一步限制角色的权限（如果不进行设置默认拥有角色的所有权限），设置权限为角色拥有权限的子集
            // 如何编写policy参考工具：https://help.aliyun.com/document_detail/155426.html
            //->withPolicy("<实际的权限policy>")
            // 连接超时时间60s
            ->connectTimeout(60)
            // 请求超时时间65s
            ->timeout(65)
            ->request();

        // construct get token url
        $result = $this->curl($this->getSignInTokenUrl(
            accessKeyId: $response->Credentials->AccessKeyId,
            accessKeySecret: $response->Credentials->AccessKeySecret,
            securityToken: $response->Credentials->SecurityToken
        ));

        $signInTokenJson = json_decode($result);

        $signInToken = $signInTokenJson->SigninToken;

        return $signInToken;
    }

    /**
     * construct get token url
     * @param $accessKeyId
     * @param $accessKeySecret
     * @param $securityToken
     */
    public function getSignInTokenUrl($accessKeyId, $accessKeySecret, $securityToken)
    {
        $signInTokenUrl = getenv('ALIYUN_SIGN_IN_HOST')."/federation?Action=GetSigninToken"
            ."&AccessKeyId=".urlencode($accessKeyId)
            ."&AccessKeySecret=".urlencode($accessKeySecret)
            ."&SecurityToken=".urlencode($securityToken)
            ."&TicketType=mini";

        return $signInTokenUrl;
    }

    /**
     * send curl http request
     * @param $signInTokenUrl
     */
    public function curl($signInTokenUrl)
    {
        $curlInit = curl_init();
        curl_setopt($curlInit, CURLOPT_URL, $signInTokenUrl);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curlInit);
        curl_close($curlInit);
        return $result;
    }
}
