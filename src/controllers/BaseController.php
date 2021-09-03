<?php

namespace app\controllers;

use yii\rest\Controller;

class BaseController extends Controller
{
    /**
     * common response function
     *
     * @param string $code
     * @param string $message
     * @param array $data
     * @param integer $status
     * @param array|null $headers
     * @return \yii\web\Response
     */
    public function response(
        string $code = '',
        string $message = '',
        array $data,
        int $status = 200,
        ?array $headers = null
    ) {
        $this->response->setStatusCode($status);

        if ($headers) {
            array_walk($headers, function ($value, $key) {
                $this->response->headers->set($key, $value);
            });
        }

        return $this->asJson([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }
}
