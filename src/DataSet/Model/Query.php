<?php

namespace BiSight\DataSet\Model;

use BiSight\Common\Model\Column;

class Query
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
}
