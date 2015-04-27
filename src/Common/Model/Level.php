<?php

namespace BiSight\Common\Model;

class Level
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
    
    private $column;
    
    public function getColumn()
    {
        return $this->column;
    }
    
    public function setColumn($column)
    {
        $this->column = $column;
    }
    
    private $nameColumn;
    
    public function getNameColumn()
    {
        return $this->nameColumn;
    }
    
    public function setNameColumn($nameColumn)
    {
        $this->nameColumn = $nameColumn;
    }
    
    public function setUniqueMembers($value)
    {
        
    }
    
}
