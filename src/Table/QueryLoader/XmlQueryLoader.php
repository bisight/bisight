<?php

namespace BiSight\Table\QueryLoader;

use BiSight\Core\TableLoader\XmlTableLoader;
use BiSight\Core\Model\Column;
use BiSight\Core\Model\Parameter;
use BiSight\Core\Model\Option;
use BiSight\Table\Model\Table;
use BiSight\Table\Model\Query;
use BiSight\Table\Model\Group;
use BiSight\Table\Model\Order;
use BiSight\Table\Model\Filter;
use SimpleXmlElement;
use RuntimeException;

class XmlQueryLoader
{
    public function loadFile($filename, $tablePath)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("Query file not found: " . $filename);
        }
        $xml = simplexml_load_file($filename);
        return $this->load($xml, $tablePath);
    }
    
    public function load(SimpleXmlElement $xml, $tablePath)
    {
        $tableName = (string)$xml['table'];
        
        $tableLoader = new XmlTableLoader();
        $filename = $tablePath . '/' . $tableName . '.xml';
        if (file_exists($filename)) {
            $table = $tableLoader->loadFile($tableName, $filename);
        } else {
            $table = new Table($tableName);
        }
        
        $query = new Query($table);
        $query->setName((string)$xml['name']);
        $query->setTitle((string)$xml['title']);
        $query->setDescription((string)$xml['description']);
        $query->clearColumns();

        foreach ($xml->column as $columnNode) {
            $columnName = (string)$columnNode['name'];
            if (@isset($columnNode['alias'])) {
                $columnName = (string)$columnNode['alias'];
            }
            if (!$table->hasColumn($columnName)) {
                $column = new Column();
                $column->setName((string)$columnNode['name']);
                $column->setAlias((string)$columnNode['alias']);
                $column->setType((string)$columnNode['type']);
                $column->setAggregator((string)$columnNode['aggregator']);
                $column->setDefined(true);
            } else {
                $column = $table->getColumn($columnName);
            }
            if (@isset($columnNode['label'])) {
                $column->setLabel((string)$columnNode['label']);
            }
            if (@isset($columnNode['description'])) {
                $column->setDescription((string)$columnNode['description']);
            }
            $query->setColumn($column);
        }
        
        foreach ($xml->filter as $filterNode) {
            $columnName = (string)$filterNode['column'];
            if (!$query->hasColumn($columnName)) {
                throw new RuntimeException("Filter column `$columnName` does not exist on this query;");
            }
            $column = $query->getColumn($columnName);
            $filter = new Filter();
            $filter->setColumn($column);
            $filter->setComparison((string)$filterNode['comparison']);
            $filter->setValue((string)$filterNode['value']);
            $query->addFilter($filter);
        }
        
        
        foreach ($xml->group as $groupNode) {
            $columnName = (string)$groupNode['column'];
            if (!$query->hasColumn($columnName)) {
                throw new RuntimeException("Group column `$columnName` does not exist on this query;");
            }
            $column = $query->getColumn($columnName);
            $group = new Group($column);
            $query->addGroup($group);
        }
        
        foreach ($xml->order as $orderNode) {
            $columnName = (string)$orderNode['column'];
            if (!$query->hasColumn($columnName)) {
                throw new RuntimeException("Order column `$columnName` does not exist on this query;");
            }
            $column = $query->getColumn($columnName);
            $order = new Order($column);
            $query->addOrder($order);
        }
        
        
        foreach ($xml->parameter as $parameterNode) {
            $parameter = new Parameter();
            $parameter->setName((string)$parameterNode['name']);
            $parameter->setLabel((string)$parameterNode['label']);
            $parameter->setDescription((string)$parameterNode['description']);
            $parameter->setType((string)$parameterNode['type']);
            $parameter->setDefault((string)$parameterNode['default']);

            foreach ($parameterNode->option as $optionNode) {
                $o = new Option();
                $o->setValue((string)$optionNode['value']);
                $o->setLabel((string)$optionNode);
                $parameter->addOption($o);
            }

            $query->addParameter($parameter);
        }
        return $query;
        
    }
}
