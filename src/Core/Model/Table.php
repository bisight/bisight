<?php

namespace BiSight\Core\Model;

class Table
{
    private $name;
    private $description;
    
    public function __construct($name = null)
    {
        $this->setName($name);
    }
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    protected $title;
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    private $columns = array();
    
    public function setColumn(Column $column)
    {
        $this->columns[$column->getName()] = $column;
    }
    
    public function getColumns()
    {
        return $this->columns;
    }
    public function hasColumn($name)
    {
        return isset($this->columns[$name]);
    }
    
    public function getColumn($name)
    {
        return $this->columns[$name];
    }
}
