<?php

namespace BiSight\DataWarehouse\Model;

use BiSight\DataWarehouse\Storage\StorageInterface;

class DataWarehouse
{
    private $storage;
    
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }
    
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
    
    public function getTables()
    {
        return $this->storage->getTables();
    }
    
    public function getStorage()
    {
        return $this->storage;
    }
}
