<?php

namespace BiSight\Engine\Repository;

use BiSight\Common\SchemaRepositoryInterface;
use BiSight\Common\Model\Schema;
use BiSight\Common\Model\DataWarehouse;

class ArrayDataWarehouseRepository implements SchemaRepositoryInterface
{
    private $dataWarehouses = array();
    
    public function __construct($config)
    {
        foreach ($config as $data) {
            $dw = new DataWarehouse();
            $dw->setCode($data['code']);
            $dw->setName($data['name']);
            foreach ($data['config'] as $key => $value) {
                $dw->setConfig($key, $value);
            }
            $schemaname = $data['schema'];
            $dw->setSchemaName($schemaname);
            
            $this->dataWarehouses[$dw->getCode()] = $dw;
        }
    }
    
    public function getByCode($code)
    {
        return $this->dataWarehouses[$code];
    }
    
    public function getAll()
    {
        return $this->dataWarehouses;
    }
}
