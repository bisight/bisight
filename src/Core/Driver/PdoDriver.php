<?php

namespace BiSight\Core\Driver;

use BiSight\Core\Driver\PdoResultSet;
use BiSight\Core\Model\Column;
use BiSight\Core\Model\Table;
use BiSight\Lattice\Model\Query;
use BiSight\Lattice\Storage\PdoStorage as PdoLatticeStorage;
use RuntimeException;
use PDO;

class PdoDriver implements DriverInterface
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
    
    public function getResultSetByTablename($tablename, Table $table)
    {
        $sql = "SELECT * FROM " . $tablename;
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute();
        
        $i = 0;
        $columns = array();
        while ($i < $stmt->columnCount()) {
            $meta = $stmt->getColumnMeta($i);
            $columnName = (string)$meta['name'];
            if ($table->hasColumn($columnName)) {
                $column = $table->getColumn($columnName);
            } else {
                $column = new Column();
                $column->setName((string)$meta['name']);
            }
            $columns[] = $column;
            $i++;
        }
        $result = new PdoResultSet($stmt, $columns);
        return $result;
        
    }
    
    public function latticeQuery(Query $q, $values = array())
    {
        $latticeStorage = new PdoLatticeStorage($this->pdo);
        $result = $latticeStorage->query($q, $values);
        return $result;
    }
}
