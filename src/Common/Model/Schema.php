<?php

namespace BiSight\Common\Model;

class Schema
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
    
    private $cubes = array();
    
    public function getCube($name)
    {
        return $this->cubes[$name];
    }
    
    public function setCube(Cube $cube)
    {
        $this->cubes[$cube->getName()] = $cube;
    }

    public function getCubes()
    {
        return $this->cubes;
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
