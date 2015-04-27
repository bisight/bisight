<?php

namespace BiSight\Common\Model;

class Measure
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
    
    public function setColumn($column)
    {
        $this->column = $column;
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
    
    private $dataType;
    
    public function getDataType()
    {
        return $this->dataType;
    }
    
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
    }
    
    private $formatString;
    
    public function getFormatString()
    {
        return $this->formatString;
    }
    
    public function setFormatString($formatString)
    {
        $this->formatString = $formatString;
    }
    
    
    
}
