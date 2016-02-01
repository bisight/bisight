<?php

namespace BiSight\Lattice\Model;

use BiSight\Common\Model\Column;

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
