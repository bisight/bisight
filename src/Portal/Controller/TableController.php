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
use BiSight\Core\TableLoader\XmlTableLoader;
use BiSight\Core\ResultSetRenderer\HtmlResultSetRenderer;
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
        
        $path = $app->getWarehouseDataModelPath($warehouse) . '/table';

        $loader = new XmlTableLoader();
        $filename = $path . '/' . $tableName . '.xml';
        if (file_exists($filename)) {
            $table = $loader->loadFile($tableName, $filename);
        } else {
            $table = new Table($tableName);
        }

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

    public function downloadAction(Application $app, Request $request, $accountName, $warehouseName, $tablename)
    {
        set_time_limit(0);
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);
        $driver = $app->getWarehouseDriver($warehouse);

        $data = array();
        $data['tablename'] = $tablename;

        $res = $driver->getResultSetByTablename($tablename);

        $excel = $this->getResultSetExcel($res, 'Table ' . $tablename);
        $format = $request->query->get('format');
        return $this->getExcelResponse($excel, $tablename, $format);
    }

}
