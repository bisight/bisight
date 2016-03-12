<?php
namespace BiSight\Portal\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use BiSight\Core\Driver\ResultSetInterface;
use BiSight\Core\Model\Table;
use BiSight\Core\Model\Column;
use BiSight\Core\Model\Parameter;
use BiSight\Core\ResultSetRenderer\HtmlResultSetRenderer;
use BiSight\Core\ResultSetRenderer\ExcelResultSetRenderer;
use BiSight\Core\Utils\ExcelUtils;
use BiSight\Lattice\Loader\XmlLoader as XmlLatticeLoader;
use BiSight\Lattice\Loader\XmlReportLoader as XmlLatticeReportLoader;
use BiSight\Lattice\Model\Lattice;
use BiSight\Lattice\Model\Join;
use BiSight\Lattice\Model\Filter;
use BiSight\Lattice\Model\Group;
use BiSight\Lattice\Model\Order;
use BiSight\Lattice\Model\Query as LatticeQuery;
use BiSight\Lattice\Model\Report;
use RuntimeException;
use PDO;


class LatticeController
{
    public function indexAction(Application $app, Request $request, $accountName, $warehouseName)
    {
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);
        $driver = $app->getWarehouseDriver($warehouse);

        $loader = new XmlLatticeLoader();

        $path = $app->getWarehouseDataModelPath($warehouse) . '/lattice';
        $lattices = array();
        foreach (glob($path . "/*.xml") as $filename) {
            $lattice = $loader->loadFile($filename);
            $lattice->setName(str_replace('.xml', '', basename($filename)));
            $lattices[] = $lattice;
        }

        $data = array();
        $data['lattices'] = $lattices;

        return new Response($app['twig']->render(
            'lattice/index.html.twig',
            $data
        ));
    }


    public function viewAction(Application $app, Request $request, $accountName, $warehouseName, $latticecode)
    {
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);
        $driver = $app->getWarehouseDriver($warehouse);

        $filename = $app->getWarehouseDataModelPath($warehouse) . '/lattice/' . $latticecode . '.xml';
        $loader = new XmlLatticeLoader();
        $lattice = $loader->loadFile($filename);
        $lattice->setName(str_replace('.xml', '', basename($filename)));

        $q = new LatticeQuery($lattice);
        foreach ($lattice->getColumns() as $column) {
            $q->addColumn($column);
        }
        
        $res = $driver->latticeQuery($q);
        //print_r($res);

        $renderer = new HtmlResultSetRenderer();

        $html = $renderer->render($res, 0, 2000);
        $data['tablehtml'] = $html;
        $data['lattice'] =  $lattice;
        $data['rowcount'] =  $res->getRowCount();
        return new Response($app['twig']->render(
            'lattice/view.html.twig',
            $data
        ));
    }

    public function downloadAction(Application $app, Request $request, $accountName, $warehouseName, $latticecode)
    {
        set_time_limit(0);
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);
        $driver = $app->getWarehouseDriver($warehouse);

        $filename = $app->getWarehouseDataModelPath($warehouse) . '/lattice/' . $latticecode . '.xml';
        $loader = new XmlLatticeLoader();
        $lattice = $loader->loadFile($filename);
        $lattice->setName(str_replace('.xml', '', basename($filename)));

        $q = new LatticeQuery($lattice);
        foreach ($lattice->getColumns() as $column) {
            $q->addColumn($column);
        }

        $res = $driver->latticeQuery($q);
        //print_r($res);
        $excel = $this->getResultSetExcel($res, 'Lattice ' . $latticecode);
        $format = $request->query->get('format');
        return ExcelUtils::getExcelResponse($excel, $latticecode, $format);
    }
    
    public function viewLatticeReportAction(Application $app, Request $request, $accountName, $warehouseName, $reportname)
    {
        set_time_limit(0);
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);
        $driver = $app->getWarehouseDriver($warehouse);

        $filename = $app->getWarehouseDataModelPath($warehouse) . '/lattice-report/' . $reportname . '.xml';

        $latticeRepo = $app->getLatticeRepository();
        $reportloader = new XmlLatticeReportLoader($latticeRepo);

        $report = $reportloader->loadFile($filename);
        $htmlwidgets = array();
        $values = array();
        foreach ($report->getParameters() as $parameter) {
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

        $ds = $report->getLattice();

        $parameters = array();
        $q = $report->getQuery();

        $res = $driver->latticeQuery($q, $values);

        $format = null;
        if ($request->request->has('download_csv')) {
            $format = 'csv';
        }
        if ($request->request->has('download_xlsx')) {
            $format = 'xlsx';
        }
        if ($request->request->has('download_html')) {
            $format = 'html';
        }

        if ($format) {
            $renderer = new ExcelResultSetRenderer();
            $excel = $renderer->render($res, 'Report ' . $report->getName());
            return ExcelUtils::getExcelResponse($excel, $report->getName(), $format);
        }

        $renderer = new HtmlResultSetRenderer();
        $html = $renderer->render($res);
        
        $data['tablehtml'] = $html;
        $data['report'] =  $report;
        $data['htmlwidgets'] = $htmlwidgets;
        return new Response($app['twig']->render(
            'latticereport/view.html.twig',
            $data
        ));
    }

    public function indexLatticeReportAction(Application $app, Request $request, $accountName, $warehouseName)
    {
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);

        $latticeRepo = $app->getLatticeRepository();

        $loader = new XmlLatticeReportLoader($latticeRepo);

        $path = $app->getWarehouseDataModelPath($warehouse) . '/lattice-report';
        $reports = array();
        foreach (glob($path . "/*.xml") as $filename) {
            $report = $loader->loadFile($filename);
            $report->setName(str_replace('.xml', '', basename($filename)));
            $reports[] = $report;
        }

        $data = array();
        $data['reports'] = $reports;

        return new Response($app['twig']->render(
            'latticereport/index.html.twig',
            $data
        ));
    }
}
