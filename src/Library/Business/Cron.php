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
        $this->model_product= $this->_getProvider("Product");
        $this->gmail_api= new Gmail_Api();
    }

    public function saveTransactionByEmail()
    {
        $connection = ConnectionManager::get('default');
        try{
            $connection->begin();
            $unread_mails = $this->gmail_api->getMailUnread();
            $transactions = $this->gmail_api->getTransaction($unread_mails);
            $list_entities = $this->model_transaction->newEntities($transactions);
            $this->model_transaction->saveMany($list_entities);
            $connection->commit();
            Log::debug("get " . count($list_entities) . " mails");
            return true;
        }catch (\Exception $e)
        {
            Log::error($e->getMessage());
            $connection->rollback();
            return false;
        }
    }

    public function backUpDatabase(){
        try{
            $mysqldumpPath = env('DB_PATH','/usr/bin/mysqldump');

            $host = env('DB_HOST','/usr/bin/mysqldump');
            $username = env('DB_USERNAME','/usr/bin/mysqldump');
            $password = env('DB_PASSWORD','/usr/bin/mysqldump');
            $database = env('DB_DATABASE','/usr/bin/mysqldump');;

            if(!is_dir(WWW_ROOT . "backup"))
            {
                mkdir(WWW_ROOT . "backup");
            }
            $backupFile = WWW_ROOT . "backup" . DS .'backup_' . date('Y-m-d_H-i-s') . '.sql';

            $command = "$mysqldumpPath -h $host -u $username --password=$password $database > $backupFile";

            exec($command);
            return true;
        }catch (\Exception $e)
        {
            Log::error($e->getMessage());
            return false;
        }
    }

    public function updateInventory()
    {
        $connection = ConnectionManager::get('default');
        try{
            $sql = "UPDATE product SET `total_qty` = `p_qty` - `q_qty`";
            $connection->execute(
                $sql,
            );
            return true;
        }catch (\Exception $e)
        {
            Log::error($e->getMessage());
            return false;
        }
    }
}
