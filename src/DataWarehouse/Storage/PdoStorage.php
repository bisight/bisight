<?php

namespace BiSight\DataWarehouse\Storage;

use BiSight\DataWarehouse\Model\PdoResultSet;
use BiSight\DataWarehouse\Model\Table;
use BiSight\DataSet\Model\Query;
use RuntimeException;
use PDO;

class PdoStorage implements StorageInterface
{
    private $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function getTables()
    {
        $sql = "SHOW TABLES";
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute();

        $tables = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $table = new Table();
            $table->setName($row[0]);
            $tables[] = $table;
        }
        return $tables;
    }
    
    public function getResultSetByTablename($tablename)
    {
        $sql = "SELECT * FROM " . $tablename;
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute();
        $result = new PdoResultSet($stmt);
        return $result;
        
    }
    
    private function getQuerySql(Query $q)
    {
        $ds = $q->getDataSet();
        $groups = $q->getGroups();
        
        $sql = 'SELECT ';
        foreach ($q->getColumns() as $column) {
            if (count($groups)==0) {
                $sqlPart = $column->getName();
            } else {
                switch (strtoupper($column->getAggregator())) {
                    case 'SUM':
                        $sqlPart = 'SUM(' . $column->getName() . ')';
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
        $sql = rtrim($sql, ', ');
        
        $sql .= "\n";
        $sql .= 'FROM ' . $ds->getTableName() . ' AS ' . $ds->getAlias() . "\n";
        
        foreach ($ds->getJoins() as $join) {
            $sql .= $join->getType() . ' JOIN ' . $join->getTableName() . ' AS ' . $join->getAlias();
            $sql .= ' ON ' . $ds->getAlias() . '.' . $join->getForeignKey() . ' = ' . $join->getAlias() . '.' . $join->getColumnName();
            $sql .= "\n";
        }
        $sql .= "\n";
        

        if (count($groups)>0) {
            $sql .= "GROUP BY ";
            foreach ($groups as $group) {
                $sql .= $group->getColumn()->getName() . ', ';
            }
        }
        $sql = rtrim($sql, ', ');
        $sql .= "\n";
        
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
    
    public function dataSetQuery(Query $q)
    {
        $sql = $this->getQuerySql($q);
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute();
        $result = new PdoResultSet($stmt, $q->getDataSet()->getColumns());
        return $result;
    }
}
