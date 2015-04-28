<?php

namespace BiSight\Olap\Model;

class Hierarchy
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
    
    private $levels = array();
    
    public function setLevel(Level $level)
    {
        $this->levels[$level->getName()] = $level;
    }
    
    public function getLevels()
    {
        return $this->levels;
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
    
}
