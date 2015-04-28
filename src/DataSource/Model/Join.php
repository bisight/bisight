<?php

namespace BiSight\DataSource\Model;

class Join
{
    private $tableName;
    
    public function getTableName()
    {
        return $this->tableName;
    }
    
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }
    
    private $columnName;
    
    public function getColumnName()
    {
        return $this->columnName;
    }
    
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;
    }
    
    private $foreignKey;
    
    public function getForeignKey()
    {
        return $this->foreignKey;
    }
    
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;
    }
    
    private $type = 'INNER';
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
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
