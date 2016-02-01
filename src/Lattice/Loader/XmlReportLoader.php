<?php

namespace BiSight\Lattice\Loader;

use BiSight\Lattice\Model\DataSet;
use BiSight\Lattice\Model\Report;
use BiSight\Lattice\Model\Join;
use BiSight\Lattice\Model\Group;
use BiSight\Lattice\Model\Order;
use BiSight\Lattice\Model\Filter;
use BiSight\Lattice\Repository\LatticeRepositoryInterface;
use BiSight\Core\Model\Column;
use BiSight\Core\Model\Parameter;
use BiSight\Core\Model\Option;
use RuntimeException;
use SimpleXMLElement;

class XmlReportLoader
{
    private $dsrepository;
    
    public function __construct(LatticeRepositoryInterface $dsrepository)
    {
        $this->dsrepository = $dsrepository;
    }
    public function loadFile($filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("DataSourceReport file not found: " . $filename);
        }
        $xml = simplexml_load_file($filename);
        return $this->load($xml);
    }
    
    public function load(SimpleXMLElement $xml)
    {
        $dsname = (string)$xml->dataset;
        $ds = $this->dsrepository->get($dsname);
        $report = new Report($ds);
        
        $report->setName((string)$xml->name);
        $report->setLabel((string)$xml->label);
        $report->setDescription((string)$xml->description);
        
        foreach ($xml->column as $columnNode) {
            $name = (string)$columnNode['name'];
            $c = $ds->getColumn($name);
            $report->addColumn($c);
        }
        
        foreach ($xml->group as $groupNode) {
            $name = (string)$groupNode['column'];
            $c = $ds->getColumn($name);
            $group = new Group($c);
            $report->addGroup($group);
        }

        foreach ($xml->order as $orderNode) {
            $name = (string)$orderNode['column'];
            $c = $ds->getColumn($name);
            $order = new Order($c);
            if ((string)$orderNode['reverse']=='true') {
                $order->setReverse();
            }
            $report->addOrder($order);
        }
        
        foreach ($xml->filter as $filterNode) {
            $name = (string)$filterNode['column'];
            $c = $ds->getColumn($name);
            $filter = new Filter();
            $filter->setColumn($c);
            $filter->setComparison((string)$filterNode['comparison']);
            $filter->setValue((string)$filterNode['value']);
            
            $report->addFilter($filter);
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

            $report->addParameter($parameter);
        }
        return $report;
    }
}
