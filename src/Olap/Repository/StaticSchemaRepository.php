<?php

namespace BiSight\Olap\Repository;

use BiSight\Olap\SchemaRepositoryInterface;
use BiSight\Olap\Model\Schema;
use BiSight\Olap\Model\Cube;
use BiSight\Olap\Model\Dimension;
use BiSight\Olap\Model\Measure;
use BiSight\Olap\Model\Hierarchy;
use BiSight\Olap\Model\Level;

class StaticSchemaRepository implements SchemaRepositoryInterface
{
    private $schemas = array();
    
    public function __construct()
    {
        $schema = new Schema();
        $schema->setName("pennyblossom");

        $cube = new Cube();
        $cube->setName("sales");
        $cube->setDescription("Sales transactions analysis cube");
        $cube->setTableName("fact_sales");
        $schema->setCube($cube);

        $dimension = new Dimension();
        $dimension->setName("Gender");
        $dimension->setForeignKey("customer_id");
        $hierarchy = new Hierarchy();
        $hierarchy->setName("(All)");
        $hierarchy->setTableName("dim_customer");
        
        $level = new Level();
        $level->setName("all");
        $level->setColumn("gender");
        $level->setNameColumn("gender_name");
        $level->setUniqueMembers(true);
        $hierarchy->setLevel($level);
        $dimension->setHierarchy($hierarchy);
        
        $schema->setDimension($dimension);
        $cube->setDimension($dimension);
        
        $dimension = new Dimension();
        $dimension->setName("order");
        $dimension->setForeignKey("order_id");
        $schema->setDimension($dimension);
        $cube->setDimension($dimension);


        $measure = new Measure();
        $measure->setName("price");
        $measure->setColumn('price');
        $measure->setDataType('money');
        $measure->setAggregator('sum');
        $measure->setFormatString('#,###.00');
        $cube->setMeasure($measure);
        
        $cube = new Cube();
        $cube->setName("stock mutation");
        $cube->setTableName("fact_stock_mutation");

        $schema->setCube($cube);
        
        
        $this->register($schema);
    }
    
    private function register(Schema $schema)
    {
        $this->schemas[$schema->getName()] = $schema;
    }
    
    public function getByName($name)
    {
        return $this->schemas[$name];
    }
}
