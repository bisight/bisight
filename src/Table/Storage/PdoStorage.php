<?php

namespace BiSight\Table\Storage;

use BiSight\Core\Driver\PdoResultSet;
use BiSight\Table\Model\Table;
use BiSight\Table\Model\Query;

use RuntimeException;
use PDO;

class PdoStorage implements StorageInterface
{
    private $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    private function getQuerySql(Query $q, $values)
    {
        $table = $q->getTable();
        $groups = $q->getGroups();
        $filters = $q->getFilters();
        
        $sql = 'SELECT ';
        foreach ($q->getColumns() as $column) {
            if (!$column->isExpression()) {
                if (count($groups)==0) {
                    $sqlPart = $column->getName();
                } else {
                    switch (strtoupper($column->getAggregator())) {
                        case 'SUM':
                            $sqlPart = 'SUM(' . $column->getName() . ')';
                            break;
                        case 'COUNT':
                            $sqlPart = 'COUNT(' . $column->getName() . ')';
                            break;
                        case '':
                            $sqlPart = $column->getName();
                            break;
                        default:
                            throw new RuntimeException("Unsupported aggregator: " . $column->getAggregator());
                    }
                }
                if ($column->getType() == 'money') {
                    $sqlPart = 'CAST(' . $sqlPart . ' AS DECIMAL(12,2))';
                }
                $sql .= $sqlPart . ' AS ' . $column->getAlias() . ', ';
            }
        }
        $sql = rtrim($sql, ', ');
        
        $sql .= "\n";
        $sql .= 'FROM ' . $table->getName() . "\n";
        
        if (count($filters)>0) {
            $sql .= "WHERE ";
            foreach ($filters as $filter) {
                //print_r($filter);
                $sql .= $filter->getColumn()->getName();
                switch ($filter->getComparison()) {
                    case 'equals':
                        $sql .= '=';
                        break;
                    case 'greater-than':
                        $sql .= '>';
                        break;
                    case 'less-than':
                        $sql .= '<';
                        break;
                    default:
                        throw new RuntimeException("Unsupported comparison: " . $filter->getComparison());
                        break;
                }
                $value = $filter->getValue();
                
                foreach ($values as $k => $v) {
                    $value = str_replace('{' . $k . '}', $v, $value);
                }
                $sql .= $value;
                $sql .= ' AND ';
            }
            $sql = substr($sql, 0, -5);
            $sql .= "\n";
        }

        if (count($groups)>0) {
            $sql .= "GROUP BY ";
            foreach ($groups as $group) {
                $sql .= $group->getColumn()->getName() . ', ';
            }
            $sql = rtrim($sql, ', ');
            $sql .= "\n";
        }
        
        $orders = $q->getOrders();
        
        if (count($orders)>0) {
            $sql .= "ORDER BY ";
            foreach ($orders as $order) {
                $sql .= $order->getColumn()->getAlias();
                if ($order->isReverse()) {
                    $sql .= ' DESC';
                }
                $sql .= ', ';
            }
        }
        $sql = rtrim($sql, ', ');
        $sql .= "\n";
        
        if ($q->getLimit()) {
            $sql .= "LIMIT " . $q->getLimit();
        }
        $sql .= "\n";
        $sql .= ';';
        $sql = str_replace("\n\n\n", "\n", $sql);
        $sql = str_replace("\n\n", "\n", $sql);
        return $sql;
    }
    
    public function query(Query $q, $values = array())
    {
        $sql = $this->getQuerySql($q, $values);
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute();
        $result = new PdoResultSet($stmt, $q->getColumns());
        return $result;
    }
}
