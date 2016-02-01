<?php

namespace BiSight\Lattice\Repository;

use BiSight\Lattice\Loader\XmlLoader;
use InvalidArgumentException;

class XmlLatticeRepository implements LatticeRepositoryInterface
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
        $lattice = $this->loader->loadFile($filename);
        $lattice->setName($name);
        return $lattice;
    }
}
