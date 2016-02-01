<?php

namespace BiSight\Core\Model;

class Option
{
    private $value;
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    private $label;
    
    public function getLabel()
    {
        if ($this->label) {
            return $this->label;
        }
        return $this->getValue();
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
}
