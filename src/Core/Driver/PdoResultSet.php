<?php

namespace BiSight\Core\Driver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use BiSight\Core\Utils\ExpressionUtils;
use PDO;

class PdoResultSet implements ResultSetInterface
{
    private $stmt;
    private $columns;
    private $language;
    private $utils;
    
    public function __construct($stmt, $columns)
    {
        $this->stmt = $stmt;
        $this->columns = $columns;
        $this->language = new ExpressionLanguage();
        $this->utils = new ExpressionUtils();
        
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
                $row['utils'] = $this->utils;
                $output = $this->language->evaluate($column->getExpression(), $row);
                $row[$column->getAlias()] = $output;
                unset($row['utils']);

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
