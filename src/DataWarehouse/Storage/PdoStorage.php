<?php

namespace BiSight\DataWarehouse\Storage;

use BiSight\Common\Storage\PdoResultSet;
use BiSight\Common\Model\Column;
use BiSight\DataWarehouse\Model\Table;
use BiSight\DataSet\Model\Query;
use BiSight\DataSet\Storage\PdoStorage as PdoDataSetStorage;
use RuntimeException;
use PDO;

class PdoStorage implements StorageInterface
{
    private $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function getTables()
    {
        $sql = "SHOW TABLES";
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute();

        $tables = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $table = new Table();
            $table->setName($row[0]);
            $tables[] = $table;
        }
        return $tables;
    }
    
    public function getResultSetByTablename($tablename)
    {
        $sql = "SELECT * FROM " . $tablename;
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute();
        
        $i = 0;
        $columns = array();
        while ($i < $stmt->columnCount()) {
            $meta = $stmt->getColumnMeta($i);
            $column = new Column();
            $column->setName((string)$meta['name']);
            $columns[] = $column;
            $i++;
        }
        $result = new PdoResultSet($stmt, $columns);
        return $result;
        
    }
    
    public function dataSetQuery(Query $q)
    {
        $dsStorage = new PdoDataSetStorage($this->pdo);
        $result = $dsStorage->query($q);
        return $result;
    }
}
