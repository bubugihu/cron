<?php

namespace App\Model\Table;

class SetProductDetailTable extends AppTable
{
    protected $condition = [];

    public function initialize(array $config): void
    {

        $this->condition = ['del_flag' => UNDEL];

        $this->hasOne("Product",[
            'foreignKey' => 'code',
            'bindingKey' => 'product_code'
        ])->setConditions(['Product.del_flag' => UNDEL]);
    }
}
