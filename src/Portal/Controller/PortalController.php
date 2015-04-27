<?php
namespace BiSight\Portal\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use BiSight\Portal\Model\Perspective;
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

        
        $sql = "SHOW TABLES";
        $stmt = $pdo->prepare($sql);
        $res = $stmt->execute();

        $tables = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            //print_r($row);
            $tables[]['name'] = $row[0];
        }
        //exit("BOOM");
        $data = array();
        //$tables[]['name'] = 'cool';
        $data['tables'] = $tables;
        
        return new Response($app['twig']->render(
            'tables/index.html.twig',
            $data
        ));
    }
    
    public function tableViewAction(Application $app, Request $request, $dwcode, $tablename)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $dbname = $dw->getConfig('dbname');
        $dm = new DatabaseManager();
        $pdo = $dm->getPdo($dbname);
        
        $data = array();
        /*
        $perspective = new Perspective();
        $perspective->setDisplayname("My custom perspective");
        $perspective->setTablename($tablename);
        $data['perspective'] = $perspective;
        */
        $data['tablename'] = $tablename;
        
        $sql = "SELECT * FROM " . $tablename;
        $stmt = $pdo->prepare($sql);
        $res = $stmt->execute();
        
        $i = 0;
        $o = '';
        $o .= '<div class="table-responsive">';
        $o .= '<table class="table table-striped table-hover table-condensed">';
        $o .= '<thead><tr>';
        while ($i < $stmt->columnCount()) {
            $meta = $stmt->getColumnMeta($i);
            $o .= "<th>" . $meta['name'] . "</th>";
            $i++;
        }
        $o .= '</tr></thead>';
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $o .= '<tr>';
            foreach ($row as $key => $value) {
                $o .= "<td>" . $value . "</td>";
            }
            $o .= '</tr>' . "\n";
        }
        
        $o .= '</table>';
        $o .= '</div>';
        $data['tablehtml'] = $o;
        
        return new Response($app['twig']->render(
            'tables/view.html.twig',
            $data
        ));
    }
    
    public function viewSchemaAction(Application $app, Request $request, $dwcode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        
        $schemarepo = $app->getSchemaRepository();
        $schema = $schemarepo->getByName($dw->getSchemaName());
        
        $data = array('schema' => $schema);
        
        return new Response($app['twig']->render(
            'schema.html.twig',
            $data
        ));
    }
}
