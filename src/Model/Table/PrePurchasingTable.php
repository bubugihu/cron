<?php

namespace App\Model\Table;

class PrePurchasingTable extends AppTable
{
    protected $condition = [];

    public function initialize(array $config): void
    {

        $this->condition = ['del_flag' => UNDEL];

    }
}
