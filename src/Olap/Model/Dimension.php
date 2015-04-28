<?php

namespace BiSight\Olap\Model;

class Dimension
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
    
    private $foreignKey;
    
    public function getForeignKey()
    {
        return $this->foreignKey;
    }
    
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;
    }
    
    private $hierarchies = array();
    
    public function setHierarchy(Hierarchy $hierarchy)
    {
        $this->hierarchies[$hierarchy->getName()] = $hierarchy;
    }
    
    public function getHierarchies()
    {
        return $this->hierarchies;
    }
}
