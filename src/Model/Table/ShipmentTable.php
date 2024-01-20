<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Log\Log;
use Cake\Validation\Validator;

class ShipmentTable extends AppTable
{
    protected $condition = [];

    public function initialize(array $config): void
    {
        $this->condition = [$this->getAlias(). '.del_flag' => UNDEL];

    }


}
