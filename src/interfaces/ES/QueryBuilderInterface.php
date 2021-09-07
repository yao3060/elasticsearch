<?php

namespace app\interfaces\ES;

interface QueryBuilderInterface
{
    public function query(): array;

    public function getRedisKey();
}
