<?php

namespace BiSight\Common\Storage;

use PDO;

class PdoResultSet implements ResultSetInterface
{
    private $stmt;
    private $columns;
    
    public function __construct($stmt, $columns)
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
        return $this->columns;
    }
}
