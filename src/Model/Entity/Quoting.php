<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Quoting extends  Entity
{
    protected $_virtual = [
        'total_display',
        'status_display',
        'price_display',
        'source_display',
    ];

    protected function _getPriceDisplay()
    {
        return number_format( (float)$this->price , 0 , '.' , ',' );
    }

    protected function _getTotalDisplay()
    {
        $total = $this->price * $this->quantity;
        return number_format( (float)$total , 0 , '.' , ',' );
    }

    protected function _getSourceDisplay()
    {
        return SOURCE[$this->source];
    }

    protected function _getStatusDisplay()
    {
        $result = "";
        switch ($this->status) {
            case STATUS_NEW:
                $status = STATUS[$this->status];
                $result = "<span class='badge text-bg-danger'>$status</span>";
                break;

            case STATUS_PROCESS:
                $status = STATUS[$this->status];
                $result = "<span class='badge text-bg-primary'>$status</span>";
                break;

            case STATUS_DONE:
                $status = STATUS[$this->status];
                $result = "<span class='badge text-bg-primary'>$status</span>";
                break;
            case STATUS_CANCEL:
                $status = STATUS[$this->status];
                $result = "<span class='badge text-bg-primary'>$status</span>";
                break;
        }
        return $result;
    }
}
