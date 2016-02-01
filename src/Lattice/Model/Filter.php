<?php

namespace BiSight\Lattice\Model;

use BiSight\Core\Model\Column;

class Filter
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
    
    public function setColumn(Column $column)
    {
        $this->column = $column;
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
