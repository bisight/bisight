<?php

namespace BiSight\Olap\Model;

class Cube
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
    
    private $description;
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    private $tableName;
    
    public function getTableName()
    {
        return $this->tableName;
    }
    
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }
    

    private $measures = array();
    
    public function setMeasure(Measure $measure)
    {
        $this->measures[$measure->getName()] = $measure;
    }
    
    public function getMeasures()
    {
        return $this->measures;
    }
        
    private $dimensions = array();

    public function getDimension($name)
    {
        return $this->dimensions[$name];
    }

    public function setDimension(Dimension $dimension)
    {
        $this->dimensions[$dimension->getName()] = $dimension;
    }

    public function getDimensions()
    {
        return $this->dimensions;
    }
}
