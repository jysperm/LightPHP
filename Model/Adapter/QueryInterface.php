<?php

namespace LightPHP\Model\Adapter;

interface QueryInterface
{
    /** @property string $primary */

    public function __construct($db, $table);

    public function select(array $if = [], array $options = []);
    public function findOne(array $if = [], array $options = []);
    public function selectArray(array $if = [], array $options = []);
    public function selectValueList($field, array $if = [], array $options = []);
    public function selectPrimaryArray(array $if = [], array $options = [], $field = null);
    public function count(array $if = [], array $options = []);

    public function insert(array $data);
    public function insertArray(array $data);
    public function update(array $if, array $data);
    public function delete(array $if);

    public function getAttribute($name);
}
