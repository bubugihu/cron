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

    public function backUpDatabase(){
        // Đường dẫn đến lệnh mysqldump (đối với MySQL)
        $mysqldumpPath = env('DB_PATH','/usr/bin/mysqldump');
        // Thông tin kết nối cơ sở dữ liệu
        $host = env('DB_HOST','/usr/bin/mysqldump');
        $username = env('DB_USERNAME','/usr/bin/mysqldump');
        $password = env('DB_PASSWORD','/usr/bin/mysqldump');
        $database = env('DB_DATABASE','/usr/bin/mysqldump');;

        if(!is_dir(WWW_ROOT . "backup"))
        {
            mkdir(WWW_ROOT . "backup");
        }
        // Tên file sao lưu
        $backupFile = WWW_ROOT . "backup" . DS .'backup_' . date('Y-m-d_H-i-s') . '.sql';

        // Tạo lệnh mysqldump
        $command = "$mysqldumpPath -h $host -u $username --password=$password $database > $backupFile";

        // Thực hiện lệnh
        exec($command);
    }
}
