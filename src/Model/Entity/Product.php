<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Product extends  Entity
{
    protected $_virtual = [
//        'category_display',
//        'unit_display',
        'total_display',
        'import_price_display',
        'export_price_display',
    ];

//    protected function _getCategoryDisplay()
//    {
//        return CATEGORY[$this->category_id];
//    }
//
//    protected function _getUnitDisplay()
//    {
//        return UNIT[$this->unit_id];
//    }

    protected function _getImportPriceDisplay()
    {
        return number_format( (float)$this->p_price , 0 , '.' , ',' );
    }

    protected function _getExportPriceDisplay()
    {
        return number_format( (float)$this->q_price , 0 , '.' , ',' );
    }

    protected function _getTotalDisplay()
    {
        return intval($this->p_qty) - intval($this->q_qty);
    }
}
