<?php

namespace BiSight\DataWarehouse\Repository;

use BiSight\DataWarehouse\Model\DataWarehouse;
use BiSight\DataWarehouse\Storage\PdoStorage;
use BiSight\Olap\SchemaRepositoryInterface;
use BiSight\Olap\Model\Schema;
use LinkORB\Component\DatabaseManager\DatabaseManager;

class ArrayDataWarehouseRepository implements SchemaRepositoryInterface
{
    private $dataWarehouses = array();

    public function __construct($config)
    {
        $dm = new DatabaseManager();
        foreach ($config as $data) {
            $dbname = $data['config']['dbname'];

            $pdo = $dm->getPdo($dbname);
            $storage = new PdoStorage($pdo);

            $dw = new DataWarehouse($storage);
            $dw->setCode($data['code']);
            $dw->setName($data['name']);
            foreach ($data['config'] as $key => $value) {
                $dw->setConfig($key, $value);
            }
            $schemaname = isset($data['schema']) ? $data['schema'] : null;
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
