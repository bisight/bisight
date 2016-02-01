<?php

namespace BiSight\Lattice\Loader;

use BiSight\Lattice\Model\Lattice;
use BiSight\Lattice\Model\Join;
use BiSight\Lattice\Model\Filter;
use BiSight\Core\Model\Column;
use RuntimeException;
use SimpleXMLElement;

class XmlLoader
{
    public function loadFile($filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("Lattice file not found: " . $filename);
        }
        $xml = simplexml_load_file($filename);
        return $this->load($xml);
    }
    
    public function load(SimpleXMLElement $xml)
    {
        $lattice = new Lattice();
        
        $lattice->setName((string)$xml->name);
        $lattice->setTableName((string)$xml->tablename);
        $lattice->setDescription((string)$xml->description);
        $lattice->setAlias((string)$xml->tablename['alias']);
        
        foreach ($xml->join as $joinNode) {
            $j = new Join();
            $j->setTableName((string)$joinNode->tablename);
            $j->setColumnName((string)$joinNode->column);
            $j->setAlias((string)$joinNode->tablename['alias']);
            $j->setForeignKey((string)$joinNode->foreignkey);
            $j->setType((string)$joinNode->type);
            $lattice->addJoin($j);
        }
        
        foreach ($xml->column as $columnNode) {
            $c = new Column();
            $c->setName((string)$columnNode->name);
            $c->setAlias((string)$columnNode->alias);
            $c->setLabel((string)$columnNode->label);
            $c->setDescription((string)$columnNode->description);
            $c->setType((string)$columnNode->type);
            $c->setAggregator((string)$columnNode->aggregator);
            $c->setExpression((string)$columnNode->expression);
            $lattice->addColumn($c);
        }
        
        return $lattice;
        
    }
}
