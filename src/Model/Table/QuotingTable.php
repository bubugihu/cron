<?php

namespace App\Model\Table;

class QuotingTable extends AppTable
{
    protected $condition = [];

    public function initialize(array $config): void
    {

        $this->condition = ['del_flag' => UNDEL];
        $this->hasOne("Product",[
            'foreignKey' => 'code',
            'bindingKey' => 'code'
        ])->setConditions(['Product.del_flag' => UNDEL]);
    }
}
