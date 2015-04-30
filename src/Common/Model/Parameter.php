<?php

namespace BiSight\Common\Model;

class Parameter
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
    
    private $label;
    
    public function getLabel()
    {
        if ($this->label) {
            return $this->label;
        }
        return $this->getName();
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
    
    private $type;
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    private $default;
    
    public function getDefault()
    {
        return $this->default;
    }
    
    public function setDefault($default)
    {
        $this->default = $default;
    }
    
    private $options = array();
    
    public function addOption(Option $option)
    {
        $this->options[$option->getValue()] = $option;
    }
    
    public function getOptions()
    {
        return $this->options;
    }
}
