<?php

namespace BiSight\DataSource\Model;

use BiSight\DataWarehouse\Model\Column;

class Query
{
    private $dataSource;
    
    public function __construct(DataSource $dataSource)
    {
        $this->dataSource = $dataSource;
    }
    
    public function getDataSource()
    {
        return $this->dataSource;
    }
    
    private $columns = array();
    
    public function addColumn(Column $column)
    {
        $this->columns[] = $column;
        return $this;
    }

    public function addColumnName($columnName)
    {
        $column = $this->dataSource->getColumn($columnName);
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
