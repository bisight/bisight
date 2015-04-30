<?php

namespace BiSight\DataSet\Loader;

use BiSight\DataSet\Model\DataSet;
use BiSight\DataSet\Model\Report;
use BiSight\DataSet\Model\Join;
use BiSight\DataSet\Model\Group;
use BiSight\DataSet\Model\Order;
use BiSight\DataSet\Model\Filter;
use BiSight\DataSet\Repository\DataSetRepositoryInterface;
use BiSight\Common\Model\Column;
use RuntimeException;
use SimpleXMLElement;

class XmlReportLoader
{
    private $dsrepository;
    
    public function __construct(DataSetRepositoryInterface $dsrepository)
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
        
        return $report;
        
    }
}
