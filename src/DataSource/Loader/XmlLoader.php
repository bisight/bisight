<?php

namespace BiSight\DataSource\Loader;

use BiSight\DataSource\Model\DataSource;
use BiSight\DataSource\Model\Join;
use BiSight\DataSource\Model\Group;
use BiSight\DataSource\Model\Filter;
use BiSight\DataSource\Model\Order;
use BiSight\DataSource\Model\Query;
use BiSight\DataWarehouse\Model\Column;
use RuntimeException;
use SimpleXMLElement;


class XmlLoader
{
    public function loadFile($filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("DataSource file not found: " . $filename);
        }
        $xml = simplexml_load_file($filename);
        return $this->load($xml);
    }
    
    public function load(SimpleXMLElement $xml)
    {
        $ds = new DataSource();
        
        $ds->setName((string)$xml->name);
        $ds->setTableName((string)$xml->tablename);
        $ds->setDescription((string)$xml->description);
        $ds->setAlias((string)$xml->tablename['alias']);
        
        foreach ($xml->join as $joinNode) {
            $j = new Join();
            $j->setTableName((string)$joinNode->tablename);
            $j->setColumnName((string)$joinNode->column);
            $j->setAlias((string)$joinNode->tablename['alias']);
            $j->setForeignKey((string)$joinNode->foreignkey);
            $j->setType((string)$joinNode->type);
            $ds->addJoin($j);
        }
        
        foreach ($xml->column as $columnNode) {
            $c = new Column();
            $c->setName((string)$columnNode->name);
            $c->setLabel((string)$columnNode->label);
            $c->setDescription((string)$columnNode->description);
            $c->setType((string)$columnNode->type);
            $c->setAggregator((string)$columnNode->aggregator);
            $ds->addColumn($c);
        }
        
        return $ds;
        
    }
}
