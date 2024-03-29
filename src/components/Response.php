<?php


namespace app\components;

use app\interfaces\ResponseInterface;

class Response implements ResponseInterface
{
    public string $code = '';
    public string $message = '';
    public array $data = [];
    public int $status = 200;
    public array $headers = [];

    function __construct(
        string $code = '',
        string $message = '',
        array $data = [],
        int $status = 200,
        array $headers = []
    ) {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function status(int $status = 200)
    {
        $this->status = $status;
    }

    public function code(string $code = '')
    {
        $this->code = $code;
    }

    public function message(string $message = '')
    {
        $this->message = $message;
    }

    public function data(array $data = [])
    {
        $this->data = $data;
    }

    public function headers(array $headers = [])
    {
        $this->headers = $headers;
    }

    public function get(string $name)
    {
        return $this->$name;
    }
}
