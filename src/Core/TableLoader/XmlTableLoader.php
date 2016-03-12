<?php

namespace BiSight\Core\TableLoader;

use BiSight\Table\Model\Table;
use BiSight\Core\Model\Column;
use SimpleXmlElement;

class XmlTableLoader
{
    public function loadFile($name, $filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("Table file not found: " . $filename);
        }
        $xml = simplexml_load_file($filename);
        return $this->load($name, $xml);
    }
    
    public function load($name, SimpleXmlElement $xml)
    {
        
        $table = new Table($name);
        $table->setTitle($xml['title']);
        $table->setDescription($xml['description']);
        $columns = array();
        foreach ($xml->column as $columnNode) {
            $column = new Column((string)$columnNode['name']);
            $column->setLabel((string)$columnNode['title']);
            $column->setType((string)$columnNode['type']);
            $column->setAggregator((string)$columnNode['aggregator']);
            $column->setDescription((string)$columnNode['description']);
            $column->setDefined(true);
            $table->setColumn($column);
        }
        return $table;
    }
}
