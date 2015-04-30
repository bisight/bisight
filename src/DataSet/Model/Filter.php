<?php

namespace BiSight\DataSet\Model;

class Join
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
    
    private $columnName;
    
    public function getColumnName()
    {
        return $this->columnName;
    }
    
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;
    }
    
    private $comparison;
    
    public function getComparison()
    {
        return $this->comparison;
    }
    
    public function setComparison($comparison)
    {
        $this->comparison = $comparison;
    }
    
    private $value;
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    private $filters;
    
    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;
    }
    
    public function getFilters()
    {
        return $this->filters;
    }
}
