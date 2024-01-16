<?php

namespace App\Library\Business;

use Cake\ORM\TableRegistry;

abstract class Entity
{
    /**
     * Instantinates the class
     */
    public function __construct() {

    }

    /**
     * Gets data provider.
     *
     * @param string $providerName name of provider
     * @return entity
     */
    protected function _getProvider($providerName) {
        return TableRegistry::getTableLocator()->get($providerName);
    }

}
