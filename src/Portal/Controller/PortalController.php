<?php
namespace BiSight\Portal\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use BiSight\Common\Storage\ResultSetInterface;
use BiSight\DataSet\Model\DataSet;
use BiSight\DataSet\Model\Join;
use BiSight\DataSet\Model\Filter;
use BiSight\DataSet\Model\Group;
use BiSight\DataSet\Model\Order;
use BiSight\DataSet\Model\Query as DataSetQuery;
use BiSight\DataWarehouse\Model\Column;
use BiSight\DataSet\Loader\XmlLoader as XmlDataSetLoader;

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
    
    public function viewDataSetAction(Application $app, Request $request, $dwcode, $dscode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $storage = $dw->getStorage();

        $filename = __DIR__ . '/../../../example/dataset/sales.xml';
        $loader = new XmlDataSetLoader();
        $ds = $loader->loadFile($filename);
        
        $c = $ds->getColumn('d.weekday');
        $g = new Group($c);


        $c = $ds->getColumn('s.price');
        $o = new Order($c);
        $o->setReverse();
        
        $q = new DataSetQuery($ds);
        $q->addColumnName('c.fullname')->addColumnName('p.name')->addColumnName('d.weekdayname')->addColumnName('s.price');
        $q->addGroup($g);
        $q->addOrder($o);
        //$q->setLimit(10);
        $q->setOffset(0);
        
        $res = $storage->dataSetQuery($q);
        //print_r($res);
        
        $html = $this->getResultSetHtml($res);
        $data['tablename'] = 'x';
        $data['tablehtml'] = $html;
        $data['dataset'] =  $ds;
        return new Response($app['twig']->render(
            'dataset/view.html.twig',
            $data
        ));
        
    }
}
