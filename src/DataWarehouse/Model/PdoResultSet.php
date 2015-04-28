<?php

namespace BiSight\DataWarehouse\Model;

use PDO;

class PdoResultSet implements ResultSetInterface
{
    private $stmt;
    private $columns;
    
    public function __construct($stmt, $columns = null)
    {
        $this->stmt = $stmt;
        $this->columns = $columns;
    }
    
    public function getRow()
    {
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getRowCount()
    {
        return (int)$this->stmt->rowCount();
    }
    
    public function getColumns()
    {
        if ($this->columns) {
            return $this->columns;
        }
        
        $columns = array();
        $i = 0;
        while ($i < $this->stmt->columnCount()) {
            $meta = $this->stmt->getColumnMeta($i);
            $column = new Column();
            $column->setName((string)$meta['name']);
            $columns[] = $column;
            $i++;
        }
        return $columns;
    }
}
