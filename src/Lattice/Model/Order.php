<?php

namespace BiSight\Lattice\Model;

use BiSight\Core\Model\Column;

class Order
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
    
    private $reverse = false;
    
    public function setReverse($reverse = true)
    {
        $this->reverse = $reverse;
    }
    
    public function isReverse()
    {
        if ($this->reverse) {
            return true;
        }
        return false;
    }
}
