<?php

namespace App\Model\Table;

class OrdersTable extends AppTable
{
    protected $condition = [];
    public function initialize(array $config): void
    {
        $this->condition = ['del_flag' => UNDEL];
        $this->setEntityClass(\App\Model\Entity\Orders::class);
        $this->hasMany("Quoting",[
            'foreignKey' => 'order_code',
            'bindingKey' => 'order_code'
        ])->setConditions(['Quoting.del_flag' => UNDEL]);
    }
}
