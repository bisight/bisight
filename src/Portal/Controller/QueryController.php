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
use BiSight\Table\Model\Table;
use BiSight\Table\Model\Query;
use BiSight\Table\Model\Filter;
use BiSight\Table\Model\Group;
use BiSight\Table\Model\Order;
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

class QueryController
{
    public function indexAction(Application $app, Request $request, $accountName, $warehouseName)
    {
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);
        $connection = $app->getWarehouseDriver($warehouse);
        $queries = $app->getWarehouseQueries($warehouse);

        $data = array();
        $data['queries'] = $queries;

        return new Response($app['twig']->render(
            'queries/index.html.twig',
            $data
        ));
    }
    
    public function viewAction(Application $app, Request $request, $accountName, $warehouseName, $queryName)
    {
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);
        $driver = $app->getWarehouseDriver($warehouse);
        $queries = $app->getWarehouseQueries($warehouse);
        $query = null;
        foreach ($queries as $q) {
            if ($q->getName()==$queryName) {
                $query = $q;
            }
        }
        if (!$query) {
            throw new RuntimeException("Query not found: " . $queryName);
        }

        $htmlwidgets = array();
        $values = array();
        foreach ($query->getParameters() as $parameter) {
            $name = $parameter->getName();
            $value = $parameter->getDefault();
            if ($request->request->has('PARAMETER_' . $name)) {
                $value = $request->request->get('PARAMETER_' . $name);
            }
            if ($parameter->getType()=='date') {
                $value = str_replace('-', '', $value);
            }

            $html = $app->getHtmlWidget($parameter, $value);
            $htmlwidgets[$name] = $html;
            $values[$name] = $value;
        }
        
        $res = $driver->tableQuery($query);

        $limit = null;
        $offset = null;
        /*
        if ($request->query->has('limit')) {
            $limit = $request->query->get('limit');
        }

        if ($request->query->has('offset')) {
            $limit = $request->query->get('offset');
        }
        */

        $renderer = new HtmlResultSetRenderer();
        
        $data['query'] = $query;
        $data['tablehtml'] = $renderer->render($res, $offset, $limit);
        $data['rowcount'] =  $res->getRowCount();
        $data['htmlwidgets'] =  $htmlwidgets;

        return new Response($app['twig']->render(
            'queries/view.html.twig',
            $data
        ));
    }
}
