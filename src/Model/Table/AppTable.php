<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\Log;
use Cake\ORM\Table;
use Exception;

class AppTable extends Table
{
    public function initialize(array $config): void
    {
    }


    /*
     * Function selectOne
     * Parameter id index Table
     * return Object entities
     * */
    public function selectOne(array $condition = [], $contain = [])
    {
        if (isset($condition['id']) && !is_numeric($condition['id'])) {
            return false;
        }
        $condition = array_merge($condition, $this->condition);
        try {
            $entity = $this->find()
                ->where($condition)->contain($contain)->first();
        }
        catch(Exception $e) {
            Log::error($e->getMessage());
            return false;
        }

        return $entity;
    }

    /**
     * Des: Select all row in database
     * @param array|null $condition. The condition for query
     *
     * @return array;
     */
    public function selectList($condition = null, $contain = null, $order = [], $select = [], $limit = null)
    {
        if (!is_null($condition))
        {
            $condition = array_merge($condition, $this->condition);
        }
        else
        {
            $condition = $this->condition;
        }

        $result = [];

        try {
            $query = $this->find()->select($select)->where($condition)->order($order);
            if (!is_null($contain)) {
                $query->contain($contain);
            }
            if (!is_null($limit)) {
                $query->limit($limit);
            }
            $result = $query->all()->toList();
        }
        catch(Exception $e) {
            Log::error('File: '. $e->getFile(). ' Line: '. $e->getLine(). ' Message: '.$e->getMessage());
            return [];
        }

        return $result;
    }

    /*
     * Function getDataPage
     * Parameter :
     *              $condition : array()
     *              $page : integer
     *              $key_search : array search in key table
     * Return arrray object
     */
    function getDataPage($page = 1, $key_search = array(), $condition = array(), $contain = [])
    {

        $condition = array_merge($condition, $this->condition);
        $arr = array();

        if (!empty($key_search))
            foreach ($key_search as $key => $value)
            {
                $arr[$key .' LIKE'] = '%' . $value . '%';
            }

        if (!empty($arr))
            $condition['OR'] = $arr;

        // Start a new query.
        $result = $this->find()->where($condition)
            ->contain($contain)
            ->limit(LIMIT)
            ->page($page);
        return $result;
    }

    public function getIndexObject($name = '', $object_data = [])
    {
        $result = [];
        if (!isset($name) || strlen(trim($name)) <= 0 || !is_array($object_data) || empty($object_data))
            return $result;
        foreach ($object_data as $k => $v)
            if (isset($v->$name))
                $result[] = $v->$name;
        return array_unique($result);
    }

    public function getCode($str) {
        $result = $this->find('list', [
            'valueField' => function ($value) use ($str) {
                return $str.($value['id'] + 1);
            }
        ])->where()->last();
        if (!$result)
            $result = $str.'1';
        return $result;
    }

    public function getData($page = 1, $condition = [], $contain = [], $select = [], $order = [], $export = false)
    {
        $condition = array_merge($condition, $this->condition);

        if($export)
        {
            return $this->find()
                ->select($select)
                ->where($condition)
                ->contain($contain)
                ->order($order);
        }
        return $this->find()
            ->select($select)
            ->where($condition)
            ->contain($contain)
            ->order($order)
            ->limit(LIMIT)
            ->page($page);
    }

}
