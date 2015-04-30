<?php

namespace BiSight\DataSet\Repository;

use BiSight\DataSet\Loader\XmlLoader;
use InvalidArgumentException;

class XmlDataSetRepository implements DataSetRepositoryInterface
{
    private $basepath;
    public function __construct(XmlLoader $loader, $basepath)
    {
        $this->basepath = $basepath;
        $this->loader = $loader;
    }
    
    public function getAll()
    {
        
    }
    
    public function get($name)
    {
        if (!$name) {
            throw new InvalidArgumentException("no name provided");
        }
        $filename = $this->basepath . '/' . $name . '.xml';
        $dataset = $this->loader->loadFile($filename);
        $dataset->setName($name);
        return $dataset;
    }
}
