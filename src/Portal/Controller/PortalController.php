<?php
namespace BiSight\Portal\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use BiSight\Portal\Model\Perspective;
use BiSight\DataSource\Model\DataSource;
use BiSight\DataSource\Model\Join;
use BiSight\DataSource\Model\Filter;
use BiSight\DataSource\Model\Group;
use BiSight\DataSource\Model\Order;
use BiSight\DataSource\Model\Query as DataSourceQuery;
use BiSight\DataWarehouse\Model\Column;
use BiSight\DataWarehouse\Model\ResultSetInterface;

use PDO;

class PortalController
{
    public function indexAction(Application $app, Request $request)
    {
        $data = array("name" => $app['name']);
        $dwrepo = $app->getDataWarehouseRepository();

        $data['datawarehouses'] = $dwrepo->getAll();

        return new Response($app['twig']->render(
            'index.html.twig',
            $data
        ));
    }

    public function viewDataWarehouseAction(Application $app, Request $request, $dwcode)
    {
        $data = array();

        return new Response($app['twig']->render(
            'datawarehouse.html.twig',
            $data
        ));
    }
    
    public function tableIndexAction(Application $app, Request $request, $dwcode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $dbname = $dw->getConfig('dbname');
        $dm = new DatabaseManager();
        $pdo = $dm->getPdo($dbname);

        $tables = $dw->getTables();

        $data = array();
        $data['tables'] = $tables;
        
        return new Response($app['twig']->render(
            'tables/index.html.twig',
            $data
        ));
    }
    
    private function getResultSetHtml(ResultSetInterface $res)
    {
        $columns = $res->getColumns();
        
        $i = 0;
        $o = '';
        $o .= '<div class="table-responsive">';
        $o .= '<table class="table table-striped table-hover table-condensed">';
        $o .= '<thead><tr>';
        foreach ($columns as $column) {
            $o .= "<th";
            if ($column->getType() == 'money') {
                $o .= " style=\"text-align: right\"";
            }
            $o .= ">" . $column->getLabel() . "</th>";
            $i++;
        }
        $o .= '</tr></thead>';
        while ($row = $res->getRow()) {
            $o .= '<tr>';
            $i = 0;
            foreach ($row as $key => $value) {
                $column = null;
                foreach ($columns as $c) {
                    if ($c->getAlias() == $key) {
                        $column = $c;
                    }
                }

                $o .= "<td";
                
                if ($column->getType() == 'money') {
                    $o .= " style=\"text-align: right\"";
                }

                $o .= ">";

                if ($column->getType() == 'money') {
                    $o .= "&euro; ";
                }

                $o .= $value;
                $o .= "</td>";
                $i++;
            }
            $o .= '</tr>' . "\n";
        }
        
        $o .= '</table>';
        $o .= '</div>';
        return $o;
    }
    
    public function tableViewAction(Application $app, Request $request, $dwcode, $tablename)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $storage = $dw->getStorage();
        
        $data = array();
        $data['tablename'] = $tablename;
        
        $res = $storage->getResultSetByTablename($tablename);
        
        
        $data['tablehtml'] = $this->getResultSetHtml($res);
        
        return new Response($app['twig']->render(
            'tables/view.html.twig',
            $data
        ));
    }
    
    public function viewOlapSchemaAction(Application $app, Request $request, $dwcode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        
        $schemarepo = $app->getSchemaRepository();
        $schema = $schemarepo->getByName($dw->getSchemaName());
        
        $data = array('schema' => $schema);
        
        return new Response($app['twig']->render(
            'olapschema.html.twig',
            $data
        ));
    }
    
    public function viewDataSourceAction(Application $app, Request $request, $dwcode, $dscode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $storage = $dw->getStorage();

        $ds = new DataSource();
        $ds->setName("Sales");
        $ds->setTableName('fact_sales');
        $ds->setDescription('All the sales');
        $ds->setAlias('s');
        
        $j = new Join();
        $j->setTableName('dim_customer');
        $j->setColumnName('id');
        $j->setAlias('c');
        $j->setForeignKey('customer_id');
        $ds->addJoin($j);
        
        $j = new Join();
        $j->setTableName('dim_product');
        $j->setColumnName('id');
        $j->setAlias('p');
        $j->setForeignKey('product_id');
        $ds->addJoin($j);
        


    

        $c = new Column();
        $c->setName('c.fullname');
        $c->setLabel('Customer name');
        $c->setDescription("This is the name of the customer, dawg");
        $c->setType('string');
        $c->setAggregator('');
        $ds->addColumn($c);
        
        $g = new Group($c);
        
        $c = new Column();
        $c->setName('p.name');
        $c->setLabel('Product name');
        $c->setDescription("This is the name of the product, dawg");
        $c->setType('string');
        $c->setAggregator('');
        $ds->addColumn($c);
        
        
        $c = new Column();
        $c->setName('s.price');
        $c->setLabel('Price');
        $c->setDescription("This is the price, dawg");
        $c->setType('money');
        $c->setAggregator('sum');
        $ds->addColumn($c);
        
        $o = new Order($c);
        $o->setReverse();
        
        $q = new DataSourceQuery($ds);
        $q->addColumnName('c.fullname')->addColumnName('p.name')->addColumnName('s.price');
        $q->addGroup($g);
        $q->addOrder($o);
        //$q->setLimit(10);
        $q->setOffset(0);

        //print_r($q);
        
        $res = $storage->dataSourceQuery($q);
        //print_r($res);
        
        $html = $this->getResultSetHtml($res);
        $data['tablename'] = 'x';
        $data['tablehtml'] = $html;
        
        return new Response($app['twig']->render(
            'tables/view.html.twig',
            $data
        ));
        
    }
}
