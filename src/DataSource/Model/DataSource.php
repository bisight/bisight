<?php

namespace BiSight\DataSource\Model;

use BiSight\DataWarehouse\Model\Column;
use RuntimeException;

class DataSource
{
    private $name;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    private $tableName;
    
    public function getTableName()
    {
        return $this->tableName;
    }
    
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }
    
    private $description;
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    
    private $columns = array();
    
    public function addColumn(Column $column)
    {
        $this->columns[$column->getName()] = $column;
    }
    
    public function getColumns()
    {
        return $this->columns;
    }
    
    public function getColumn($name)
    {
        if (!isset($this->columns[$name])) {
            throw new RuntimeException("No such columnname on this dataset: " . $name);
        }
        return $this->columns[$name];
    }
    
    
    private $joins = array();
    
    public function addJoin(Join $join)
    {
        $this->joins[] = $join;
    }
    
    public function getJoins()
    {
        return $this->joins;
    }
    
    private $alias;
    
    public function getAlias()
    {
        return $this->alias;
    }
    
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }
}