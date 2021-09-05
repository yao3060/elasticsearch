<?php

namespace app\controllers;

use app\interfaces\ResponseInterface;
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
    public function response(ResponseInterface $response)
    {

        $this->response->setStatusCode($response->get('status'));

        if (count($response->headers)) {
            array_walk($response->headers, function ($value, $key) {
                $this->response->headers->set($key, $value);
            });
        }

        return $this->asJson([
            'code' => $response->get('code'),
            'message' => $response->get('message'),
            'data' => $response->get('data')
        ]);
    }
}
