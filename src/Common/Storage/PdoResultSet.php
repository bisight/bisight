<?php

namespace BiSight\Common\Storage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use PDO;

class PdoResultSet implements ResultSetInterface
{
    private $stmt;
    private $columns;
    private $language;
    
    public function __construct($stmt, $columns)
    {
        $this->stmt = $stmt;
        $this->columns = $columns;
        $this->language = new ExpressionLanguage();
        
    }
    
    public function getRow()
    {
        $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        foreach ($this->columns as $column) {
            if ($column->isExpression()) {
                //$row[$column->getAlias()] = 9;
                $output = $this->language->evaluate($column->getExpression(), $row);
                $output = round($output);
                $row[$column->getAlias()] = $output;

            }
        }
        return $row;
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
