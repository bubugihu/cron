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
        $this->model_transaction = $this->_getProvider("Transaction");
        $this->model_product = $this->_getProvider("Product");
        $this->model_purchasing = $this->_getProvider("Purchasing");
        $this->model_prepurchasing = $this->_getProvider("PrePurchasing");
        $this->model_quoting = $this->_getProvider("Quoting");
        $this->model_order = $this->_getProvider("Orders");
        $this->model_set_product = $this->_getProvider("SetProduct");
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
            $connection->begin();
            $sql = "UPDATE product SET `total_qty` = `p_qty` - `q_qty`";
            $connection->execute(
                $sql,
            );
            // check pre purchasing
            //get all pre purchasing
            $list_pre_purchasing = $this->model_prepurchasing->find('list', [
                'fields' => ['id', 'name', 'code' , 'quantity', 'price', 'note', 'status', 'source', 'del_flag', 'active', 'p_date'],
                'conditions' => ['PrePurchasing.del_flag' => UNDEL],
                'keyField' => 'id',
                'valueField' => function($value) {
                    return $value;
                },
            ])->toList();

            $list_product_total_empty = $this->model_product->find('list', [
                'fields' => ['id', 'total_qty', 'code', 'del_flag'],
                'conditions' => ['Product.total_qty <=' => 0],
                'keyField' => 'code',
                'valueField' => function($value) {
                    return $value;
                },
            ])->toArray();

            $list_purchasing = [];
            foreach($list_pre_purchasing as $key => $value)
            {
                if(in_array($value->code, array_keys($list_product_total_empty)))
                {
                    //insert to purchasing
                    $list_purchasing[] = $value->toArray();
                    $value->del_flag = DELETE;

                    //delete pre purchasing
                    $this->model_prepurchasing->save($value);

                    //update inventory
                    $name = $value->name ?? "";
                    $qty = $value->quantity ?? 0;
                    $price = $value->price ?? 0;
                    $code = $value->code ?? "";
                    $sql = "UPDATE product SET `name` = :name,`p_qty` = p_qty + $qty, `p_price` = $price WHERE `code` = '$code'";
                    $connection->execute(
                        $sql,['name' => $name]
                    );
                }
            }
            //save purchasing
            $list_entities_purchasing = $this->model_purchasing->newEntities($list_purchasing);
            $this->model_purchasing->saveMany($list_entities_purchasing);

            $connection->commit();
            return true;
        }catch (\Exception $e)
        {
            Log::error($e->getMessage());
            $connection->rollback();
            return false;
        }
    }

    public function confirmOrder()
    {
        $connection = ConnectionManager::get('default');
        try{
            $connection->begin();
            $list_order_waiting = $this->model_order->find('list', [
                'fields' => ['id', 'order_code'],
                'conditions' => ['Orders.status' => 0],
                'keyField' => 'id',
                'valueField' => function($value) {
                    return $value['order_code'];
                },
            ])->all()->toList();

            $this->updateQuotingInventory($list_order_waiting);

            $setFields = [
                'status'  => 2 // status done
            ];
            $where = [
                'status'  => 0 // status waiting
            ];
            // update status order
            $list_order_waiting_count = $this->model_order->updateAll($setFields, $where);

            // update status quoting
            $this->model_quoting->updateAll($setFields,$where);

            $connection->commit();
            return $list_order_waiting_count;

        }catch (\Exception $e)
        {
            Log::error($e->getMessage());
            $connection->rollback();
            return false;
        }
    }

    public function updateQuotingInventory($list_order_waiting)
    {

        // update inventory
        $list_entities = $this->model_quoting->selectList(['order_code IN ' => $list_order_waiting, 'status' => 0]);

        $list_set_product = $this->model_set_product->find('list', [
            'fields' => ['id', 'code','del_flag'],
            'conditions' => ['SetProduct.del_flag' => UNDEL],
            'keyField' => 'code',
            'valueField' => function($value) {
                return $value;
            },
        ])->contain(['SetProductDetail'])->toArray();

        $list_product = $this->model_product->find('list', [
            'fields' => ['id', 'code','del_flag'],
            'conditions' => ['Product.del_flag' => UNDEL],
            'keyField' => 'id',
            'valueField' => function($value) {
                return $value['code'];
            },
        ])->toArray();

        $connection = ConnectionManager::get('default');
        foreach($list_entities as $value)
        {
            $qty = $value['quantity'];
            $code = $value['code'];
            $price = $value['price'];
            $name = $value['name'];
            if(empty($code))
                continue;
            if(in_array($code, $list_product) || in_array($code,array_keys($list_set_product)))
            {
                if(!in_array($code,array_keys($list_set_product)))
                {
                    $sql = "UPDATE product SET `name` = :name, `q_qty` = q_qty + $qty, `q_price` = $price WHERE `code` = '$code'";
                    $connection->execute(
                        $sql,['name' => $name]
                    );
                }else{
                    $list_product_detail = $list_set_product[$code]->set_product_detail;
                    foreach($list_product_detail as $val)
                    {
                        $qty_set_detail = $val['quantity'];
                        $code_set_detail = $val['product_code'];
                        $sql = "UPDATE product SET `q_qty` = q_qty + $qty_set_detail WHERE `code` = '$code_set_detail'";
                        $connection->execute(
                            $sql,
                        );
                    }
                }
            }else{
                $params = [
                    'code'  => $code,
                    'name'  => $name,
                    'q_price'   => $price,
                    'q_qty'     => $qty,
                ];
                $new_product = $this->model_product->newEntity($params);
                $this->model_product->save($new_product);
            }

        }//endforeach
    }
}
