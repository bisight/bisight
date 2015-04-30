<?php

namespace BiSight\DataSet\Model;

use BiSight\DataWarehouse\Model\Column;

class Group
{
    private $column;
    
    public function __construct(Column $column)
    {
        $this->column = $column;
    }
    
    public function getColumn()
    {
        return $this->column;
    }
}
