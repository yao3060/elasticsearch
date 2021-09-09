<?php

namespace app\interfaces\ES;

interface QueryBuilderInterface
{
    /**
     * chain es search query
     *
     * @return array
     */
    public function query(): array;

    /**
     * Get Redis Key
     *
     * @return string
     */
    public function getRedisKey();
}
