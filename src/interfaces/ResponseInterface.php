<?php

namespace app\interfaces;


interface ResponseInterface
{
  public function status(int $status = 200);

  public function code(string $code = '');

  public function message(string $message = '');

  public function data(array $data = []);

  public function headers(array $headers = []);

  public function get(string $name);
}
