<?php

namespace BiSight\Lattice\Model;

use BiSight\Core\Model\Column;
use BiSight\Core\Model\Parameter;
use BiSight\Lattice\Model\Query;

class Report
{
    private $lattice;
    
    public function __construct(Lattice $lattice)
    {
        $this->lattice = $lattice;
    }
    
    public function getLattice()
    {
        return $this->lattice;
    }
    
    private $name;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }

    private $label;
    
    public function getLabel()
    {
        return $this->label;
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
    
    
    private $columns = array();
    
    public function addColumn(Column $column)
    {
        $this->columns[] = $column;
        return $this;
    }

    public function addColumnName($columnName)
    {
        $column = $this->dataSet->getColumn($columnName);
        $this->addColumn($column);
        return $this;
    }
    
    public function getColumns()
    {
        return $this->columns;
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
    
    public function getQuery()
    {
        $q = new Query($this->lattice);
        foreach ($this->columns as $column) {
            $q->addColumn($column);
        }
        foreach ($this->groups as $group) {
            $q->addGroup($group);
        }
        foreach ($this->orders as $order) {
            $q->addOrder($order);
        }
        foreach ($this->filters as $filter) {
            $q->addFilter($filter);
        }
        return $q;
        // construct a Query object based on provided parameters
    }
}
