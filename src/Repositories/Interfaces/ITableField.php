<?php

namespace tkouleris\CrudPanel\Repositories\Interfaces;

interface ITableField
{
    public function create( $data );
    public function list( $filter = null);
}
