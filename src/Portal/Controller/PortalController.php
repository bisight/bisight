<?php
namespace BiSight\Portal\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use BiSight\Common\Storage\ResultSetInterface;
use BiSight\Common\Model\Parameter;
use BiSight\DataSet\Model\DataSet;
use BiSight\DataSet\Model\Join;
use BiSight\DataSet\Model\Filter;
use BiSight\DataSet\Model\Group;
use BiSight\DataSet\Model\Order;
use BiSight\DataSet\Model\Query as DataSetQuery;
use BiSight\DataSet\Model\Report;
use BiSight\DataWarehouse\Model\Column;
use BiSight\DataSet\Loader\XmlLoader as XmlDataSetLoader;
use BiSight\DataSet\Loader\XmlReportLoader as XmlDataSetReportLoader;

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
    
    private function getResultSetHtml(ResultSetInterface $res, $offset = 0, $limit = null)
    {
        if (!$limit) {
            $limit = 100; // default
        }
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
        $i = 0;
        while ($row = $res->getRow()) {
            if ($i < $limit && $i >= $offset) {
                $o .= '<tr>';
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
                }
                $o .= '</tr>' . "\n";
            }
            $i++;
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
        
        $limit = null;
        if ($request->query->has('limit')) {
            $limit = $request->query->get('limit');
        }

        $offset = null;
        if ($request->query->has('offset')) {
            $limit = $request->query->get('offset');
        }

        $data['tablehtml'] = $this->getResultSetHtml($res, $offset, $limit);
        $data['rowcount'] =  $res->getRowCount();

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
    
    public function indexDataSetAction(Application $app, Request $request, $dwcode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $dbname = $dw->getConfig('dbname');
        $dm = new DatabaseManager();
        $pdo = $dm->getPdo($dbname);

        $loader = new XmlDataSetLoader();
        
        $path = $app['bisight.datamodelpath'] . '/dataset';
        $datasets = array();
        foreach (glob($path . "/*.xml") as $filename) {
            $ds = $loader->loadFile($filename);
            $ds->setName(str_replace('.xml', '', basename($filename)));
            $datasets[] = $ds;
        }
        
        $data = array();
        $data['datasets'] = $datasets;
        
        return new Response($app['twig']->render(
            'dataset/index.html.twig',
            $data
        ));
    }

    
    public function viewDataSetAction(Application $app, Request $request, $dwcode, $dscode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $storage = $dw->getStorage();
        
        $filename = $app['bisight.datamodelpath'] . '/dataset/' . $dscode . '.xml';
        $loader = new XmlDataSetLoader();
        $ds = $loader->loadFile($filename);
        $ds->setName(str_replace('.xml', '', basename($filename)));

        /*
        $c = $ds->getColumn('d.weekday');
        $g = new Group($c);

        $c = $ds->getColumn('s.price');
        $o = new Order($c);
        $o->setReverse();
        */
        
        $q = new DataSetQuery($ds);
        foreach ($ds->getColumns() as $column) {
            $q->addColumn($column);
        }
        
        $res = $storage->dataSetQuery($q);
        //print_r($res);
        
        $html = $this->getResultSetHtml($res, 0, 100);
        $data['tablehtml'] = $html;
        $data['dataset'] =  $ds;
        $data['rowcount'] =  $res->getRowCount();
        return new Response($app['twig']->render(
            'dataset/view.html.twig',
            $data
        ));
        
    }
    
    
    public function viewReportAction(Application $app, Request $request, $dwcode, $reportname)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $storage = $dw->getStorage();

        $filename = $app['bisight.datamodelpath'] . '/report/' . $reportname . '.xml';

        $dsrepo = $app->getDataSetRepository();
        $reportloader = new XmlDataSetReportLoader($dsrepo);
        
        $report = $reportloader->loadFile($filename);
        
        $values = array();
        foreach ($report->getParameters() as $parameter) {
            $name = $parameter->getName();
            $value = $parameter->getDefault();
            if ($request->request->has('PARAMETER_' . $name)) {
                $value = $request->request->get('PARAMETER_' . $name);
            }
            $htmlvalue = $value;
            if ($parameter->getType()=='date') {
                $value = str_replace('-', '', $value);
                $htmlvalue = substr($value, 0, 4) . '-' . substr($value, 4,2)  . '-' . substr($value, 6,2);
            }
            $htmlvalues[$name] = $htmlvalue;
            $values[$name] = $value;
        }

        $ds = $report->getDataSet();
        
        $parameters = array();
        $q = $report->getQuery();

        $res = $storage->dataSetQuery($q, $values);
        
        $html = $this->getResultSetHtml($res);
        $data['tablehtml'] = $html;
        $data['report'] =  $report;
        $data['htmlvalues'] =  $htmlvalues;
        return new Response($app['twig']->render(
            'report/view.html.twig',
            $data
        ));
    }
    
    public function indexReportAction(Application $app, Request $request, $dwcode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $dbname = $dw->getConfig('dbname');
        $dm = new DatabaseManager();
        $pdo = $dm->getPdo($dbname);
        
        
        $dsrepo = $app->getDataSetRepository();

        $loader = new XmlDataSetReportLoader($dsrepo);
        
        $path = $app['bisight.datamodelpath'] . '/report';
        $datasets = array();
        foreach (glob($path . "/*.xml") as $filename) {
            $report = $loader->loadFile($filename);
            $report->setName(str_replace('.xml', '', basename($filename)));
            $reports[] = $report;
        }
        
        $data = array();
        $data['reports'] = $reports;
        
        return new Response($app['twig']->render(
            'report/index.html.twig',
            $data
        ));
    }
}
