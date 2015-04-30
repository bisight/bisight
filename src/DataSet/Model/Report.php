<?php

namespace BiSight\DataSet\Model;

use BiSight\Common\Model\Column;
use BiSight\DataSet\Model\Query;

class Report
{
    private $dataSet;
    
    public function __construct(DataSet $dataSet)
    {
        $this->dataSet = $dataSet;
    }
    
    public function getDataSet()
    {
        return $this->dataSet;
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
    
    public function getQuery($parameters)
    {
        $q = new Query($this->dataSet);
        foreach ($this->columns as $column) {
            $q->addColumn($column);
        }
        foreach ($this->groups as $group) {
            $q->addGroup($group);
        }
        foreach ($this->orders as $order) {
            $q->addOrder($order);
        }
        return $q;
        // construct a Query object based on provided parameters
    }
}
