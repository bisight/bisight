<?php
namespace BiSight\Portal\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use BiSight\Core\Driver\ResultSetInterface;
use BiSight\Core\Model\Parameter;
use BiSight\Core\Model\Table;
use BiSight\Core\Model\Column;
use BiSight\Core\ResultSetRenderer\HtmlResultSetRenderer;
use BiSight\Core\ResultSetRenderer\ExcelResultSetRenderer;
use BiSight\Core\Utils\ExcelUtils;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use BiSight\Core\Utils\ExpressionUtils;
use PHPExcel;
use PHPExcel_IOFactory;
use RuntimeException;
use PDO;

class TableController
{
    public function indexAction(Application $app, Request $request, $accountName, $warehouseName)
    {
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);
        $connection = $app->getWarehouseDriver($warehouse);
        $tables = $connection->getTables();

        $data = array();
        $data['tables'] = $tables;

        return new Response($app['twig']->render(
            'tables/index.html.twig',
            $data
        ));
    }

    public function viewAction(Application $app, Request $request, $accountName, $warehouseName, $tableName)
    {
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);
        $driver = $app->getWarehouseDriver($warehouse);

        $data = array();
        
        $table = $app->getTable($warehouse, $tableName);

        $res = $driver->getResultSetByTablename($tableName, $table);

        $limit = null;
        if ($request->query->has('limit')) {
            $limit = $request->query->get('limit');
        }

        $offset = null;
        if ($request->query->has('offset')) {
            $limit = $request->query->get('offset');
        }

        $renderer = new HtmlResultSetRenderer();
        
        $data['table'] = $table;
        $data['tablehtml'] = $renderer->render($res, $offset, $limit);
        $data['rowcount'] =  $res->getRowCount();

        return new Response($app['twig']->render(
            'tables/view.html.twig',
            $data
        ));
    }

    public function downloadAction(Application $app, Request $request, $accountName, $warehouseName, $tableName)
    {
        set_time_limit(0);
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);
        $driver = $app->getWarehouseDriver($warehouse);

        $data = array();
        $data['tablename'] = $tableName;
        
        $table = $app->getTable($warehouse, $tableName);

        $res = $driver->getResultSetByTablename($tableName, $table);

        $renderer = new ExcelResultSetRenderer();
        $offset = 0;
        $limit = 10000;
        $excel = $renderer->render($res, $offset, $limit);

        $format = $request->query->get('format');
        return ExcelUtils::getExcelResponse($excel, $tableName, $format);
    }

    public function descriptionAction(Application $app, Request $request, $accountName, $warehouseName, $tableName)
    {
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);

        $data = array();
        $data['tablename'] = $tableName;
        
        $table = $app->getTable($warehouse, $tableName);

        $data['table'] = $table;
    
        return new Response($app['twig']->render(
            'tables/description.html.twig',
            $data
        ));
    }
}
