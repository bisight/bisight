<?php

namespace BiSight\Table\Model;

use BiSight\Core\Model\Column;
use BiSight\Core\Model\Parameter;

class Query
{
    private $name;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    private $title;
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    
    private $description;
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    private $table;
    private $columns = array();
    
    public function __construct(Table $table)
    {
        $this->table = $table;
        foreach ($table->getColumns() as $column) {
            $this->setColumn($column);
        }
    }
    
    public function clearColumns()
    {
        $this->columns = [];
    }
    
    public function getTable()
    {
        return $this->table;
    }
    
    public function setColumnNames($names = array())
    {
        $this->columns = [];
        
        foreach ($names as $name) {
            $column = $this->table->getColumn($columnName);
            $this->setColumn($column);
        }
        return $this;
    }
    
    public function setColumn(Column $column)
    {
        $this->columns[(string)$column->getAlias()] = $column;
    }
    
    public function getColumns()
    {
        return $this->columns;
    }
    
    public function hasColumn($name)
    {
        return @isset($this->columns[$name]);
    }
    
    public function getColumn($name)
    {
        return $this->columns[$name];
    }
    
    private $groups = array();
    
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
        return $this;
    }
    
    public function getGroups()
    {
        return $this->groups;
    }
    
    private $filters = array();

    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    private $orders = array();

    public function addOrder(Order $order)
    {
        $this->orders[] = $order;
        return $this;
    }

    public function getOrders()
    {
        return $this->orders;
    }
    
    public $limit;
    
    public function getLimit()
    {
        return $this->limit;
    }
    
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
    
    private $offset;
    
    public function getOffset()
    {
        return $this->offset;
    }
    
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
    
    private $parameters = array();

    public function addParameter(Parameter $parameter)
    {
        $this->parameters[] = $parameter;
        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}
