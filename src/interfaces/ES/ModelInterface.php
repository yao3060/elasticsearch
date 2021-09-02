<?php

namespace app\interfaces\ES;

interface ModelInterface
{

    public function search(QueryBuilderInterface $query): array;

    // public function getKeyWordResult();

    // public function searchSy();

    // public function insertRecord();

    // public function saveRecord($data);

    // public function updateRecord($id, $data): bool;

    // public function deleteRecord($id): bool;

}
