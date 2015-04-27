<?php

namespace BiSight\Common\Model;

class DataWarehouse
{
    private $code;
    
    public function getCode()
    {
        return $this->code;
    }
    
    public function setCode($code)
    {
        $this->code = $code;
    }
    
    private $name;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    private $config = array();
    
    public function getConfig($key)
    {
        return $this->config[$key];
    }
    
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
    }
    
    public function getConfigs()
    {
        return $this->config;
    }
    
    private $schemaName;
    
    public function getSchemaName()
    {
        return $this->schemaName;
    }
    
    public function setSchemaName($schemaName)
    {
        $this->schemaName = $schemaName;
    }
}
