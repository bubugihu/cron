<?php

namespace App\Model\Table;

class SetProductTable extends AppTable
{
    protected $condition = [];

    public function initialize(array $config): void
    {

        $this->condition = ['del_flag' => UNDEL];

        $this->hasMany("SetProductDetail",[
            'foreignKey' => 'set_product_code',
            'bindingKey' => 'code'
        ])->setConditions(['SetProductDetail.del_flag' => UNDEL]);
    }
}
