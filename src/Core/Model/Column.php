<?php

namespace BiSight\Core\Model;

class Column
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
    
    private $alias;
    
    public function getAlias()
    {
        if (!$this->alias) {
            $part = explode('.', $this->name);
            if (count($part)>1) {
                return $part[1];
            }
            return $part[0];
        }
        return $this->alias;
    }
    
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }
    
    private $label;
    
    public function getLabel()
    {
        if ($this->label) {
            return $this->label;
        }
        return $this->getAlias();
    }
    
    public function setLabel($label)
    {
        $this->label = $label;
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
    
    private $aggregator;
    
    public function getAggregator()
    {
        return $this->aggregator;
    }
    
    public function setAggregator($aggregator)
    {
        $this->aggregator = $aggregator;
    }
    
    private $type;
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    private $foreignTable;
    
    public function getForeignTable()
    {
        return $this->foreignTable;
    }
    
    public function setForeignTable($foreignTable)
    {
        $this->foreignTable = $foreignTable;
    }
    
    private $foreignColumn;
    
    public function getForeignColumn()
    {
        return $this->foreignColumn;
    }
    
    public function setForeignColumn($foreignColumn)
    {
        $this->foreignColumn = $foreignColumn;
    }
    
    private $expression;
    
    public function getExpression()
    {
        return $this->expression;
    }
    
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }
    
    public function isExpression()
    {
        if ($this->expression != '') {
            return true;
        }
        return false;
    }
}
