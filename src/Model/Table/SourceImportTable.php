<?php

namespace App\Model\Table;

class SourceImportTable extends AppTable
{
    protected $condition = [];

    public function initialize(array $config): void
    {

        $this->condition = ['del_flag' => UNDEL, 'active' => ACTIVE];

    }
}

