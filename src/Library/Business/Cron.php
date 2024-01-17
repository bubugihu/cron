<?php

namespace App\Library\Business;

use App\Library\Api\Gmail_Api;
use Cake\Chronos\Date;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;

class Cron extends Entity
{
    const ACCOUNT_BU = "24692047";
    public function __construct()
    {
        parent::__construct();
        $this->model_transaction= $this->_getProvider("Transaction");
        $this->gmail_api= new Gmail_Api();
    }

    public function saveTransactionByEmail()
    {
        try{
            $connection = ConnectionManager::get('default');
            $connection->begin();
            $unread_mails = $this->gmail_api->getMailUnread();
            $transactions = $this->gmail_api->getTransaction($unread_mails);
            $list_entities = $this->model_transaction->newEntities($transactions);
            $this->model_transaction->saveMany($list_entities);
            $connection->commit();
            Log::debug("get " . count($list_entities) . " mails");
        }catch (\Exception $e)
        {
            Log::error($e->getMessage());
            $connection->rollback();
        }
    }
}
