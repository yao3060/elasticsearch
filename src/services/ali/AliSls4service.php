<?php


namespace app\services\ali;

use app\helpers\StringHelper;

class AliSls4service
{
    private string $projectName;

    private string $logStoreName;

    public function __construct(
        public string $prefix = 'https://sls4service.console.aliyun.com/lognext/project/',
        private array $otherParams = [
            'hideSidebar' => 'true',
            'hideTopbar' => 'true',
            'hiddenBack' => 'true',
            'hiddenChangeProject' => 'true',
            'hiddenOverview' => 'true',
            'ignoreTabLocalStorage' => 'true'
        ],
        private string $service = 'dashboard'
    ) {
    }

    /**
     * @param  string  $projectName
     */
    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;

        return $this;
    }

    /**
     * @param  string  $logStoreName
     */
    public function setLogStoreName($logStoreName)
    {
        $this->logStoreName = $logStoreName;

        return $this;
    }

    /**
     * @param  string  $service
     * @eg: logsearch（页面） | dashboard（仪表盘）
     */
    public function setService(string $service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * setting other params
     * @param  array  $params
     */
    public function setOtherParams(array $params = [])
    {
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $newKey = StringHelper::camel($key);
                $this->otherParams[$newKey] = $value;
            }
        }

        return $this;
    }

    /**
     * 日志服务控制台内嵌参数
     * https://sls4service.console.aliyun.com/lognext/project/${ProjectName}/logsearch/${LogstoreName}?参数1&参数2
     */
    public function buildUrl(): string
    {
        $url = $this->prefix.$this->projectName."/{$this->service}/".$this->logStoreName;

        if (!empty($this->otherParams)) {
            $url .= '?'.http_build_query($this->otherParams);
        }

        return $url;
    }
}
