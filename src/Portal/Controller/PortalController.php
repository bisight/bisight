<?php
namespace BiSight\Portal\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use BiSight\Common\Storage\ResultSetInterface;
use BiSight\Common\Model\Parameter;
use BiSight\Lattice\Model\Lattice;
use BiSight\Lattice\Model\Join;
use BiSight\Lattice\Model\Filter;
use BiSight\Lattice\Model\Group;
use BiSight\Lattice\Model\Order;
use BiSight\Lattice\Model\Query as LatticeQuery;
use BiSight\Lattice\Model\Report;
use BiSight\DataWarehouse\Model\Column;
use BiSight\Lattice\Loader\XmlLoader as XmlLatticeLoader;
use BiSight\Lattice\Loader\XmlReportLoader as XmlLatticeReportLoader;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use BiSight\Common\ExpressionUtils;
use PHPExcel;
use PHPExcel_IOFactory;
use RuntimeException;
use PDO;

class PortalController
{
    public function indexAction(Application $app, Request $request)
    {
        $data = array("name" => $app['name']);
        $dwrepo = $app->getDataWarehouseRepository();
        $token = $app['security']->getToken();
        $user = $token->getUser();

        $dws = array();
        foreach ($dwrepo->getAll() as $dw) {
            if ($user->hasRole('ROLE_' . $dw->getCode())) {
                $dws[] = $dw;
            }
        }
        $data['datawarehouses'] = $dws;

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

    private function getResultSetExcel(ResultSetInterface $res, $setname)
    {
        $excel = new PHPExcel();

        $properties = $excel->getProperties();
        $properties->setCreator("BiSight Portal");
        $properties->setLastModifiedBy("BiSight Portal");
        $properties->setTitle($setname);
        $properties->setSubject($setname);
        //$properties->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        //$properties->setKeywords("office 2007 openxml php");
        //$properties->setCategory("Test result file");

        $sheet = $excel->setActiveSheetIndex(0);

        $columns = $res->getColumns();

        $i = 0;
        foreach ($columns as $column) {
            $sheet->setCellValueByColumnAndRow($i, 1, $column->getLabel());
            $i++;
        }

        $rowIndex = 2;
        while ($row = $res->getRow()) {
            $i = 0;
            foreach ($row as $key => $value) {
                $sheet->setCellValueByColumnAndRow($i, $rowIndex, $value);
                $i++;
            }
            $rowIndex++;
        }
        $sheet->setTitle($setname);
        return $excel;
    }

    private function getExcelResponse($excel, $setname, $format)
    {
        switch ($format) {
            case 'xlsx':
                $filename = $setname . '.xlsx';
                $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                break;
            case 'csv':
                $filename = $setname . '.csv';
                $writer = PHPExcel_IOFactory::createWriter($excel, 'CSV');
                break;
            case 'html':
                $filename = $setname . '.html';
                $writer = PHPExcel_IOFactory::createWriter($excel, 'HTML');
                break;
            default:
                throw new RuntimeException("Unsupported format: " . $format);
        }

        $tmpfile = tempnam('/tmp', 'bisight_download_');
        $writer->save($tmpfile);

        $response = new BinaryFileResponse($tmpfile);

        $d = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Disposition', $d);
        return $response;
    }

    private function getResultSetHtml(ResultSetInterface $res, $offset = 0, $limit = null)
    {
        if (!$limit) {
            $limit = 10000; // default
        }
        $columns = $res->getColumns();

        $language = new ExpressionLanguage();
        $utils = new ExpressionUtils();

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
                $rowData = array();
                foreach ($row as $key => $value) {
                    $rowData[$key]=(int)$value;
                }
                
                foreach ($row as $key => $value) {
                    $column = null;
                    foreach ($columns as $c) {
                        if ($c->getAlias() == $key) {
                            $column = $c;
                        }
                    }
                    
                    if ($column->isExpression()) {
                        $rowData['utils'] = $utils;
                        
                        $value = $language->evaluate($column->getExpression(), $rowData);
                        //print_r($rowData); echo $column->getExpression();
                        //echo "VALUE: " . $value;
                    }

                    $o .= "<td";

                    if ($column->getType() == 'money') {
                        $o .= " style=\"text-align: right\"";
                    }

                    $o .= ">";

                    if ($column->getType() == 'money') {
                        $o .= "&euro; ";
                        if ($value=='') {
                            $value = 0.00;
                        }
                    }

                    $o .= nl2br($value);
                    $o .= "</td>";
                }
                $o .= '</tr>' . "\n";
            }
            //exit();
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

    public function tableDownloadAction(Application $app, Request $request, $dwcode, $tablename)
    {
        set_time_limit(0);
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $storage = $dw->getStorage();

        $data = array();
        $data['tablename'] = $tablename;

        $res = $storage->getResultSetByTablename($tablename);

        $excel = $this->getResultSetExcel($res, 'Table ' . $tablename);
        $format = $request->query->get('format');
        return $this->getExcelResponse($excel, $tablename, $format);
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

    public function indexLatticeAction(Application $app, Request $request, $dwcode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $dbname = $dw->getConfig('dbname');
        $dm = new DatabaseManager();
        $pdo = $dm->getPdo($dbname);

        $loader = new XmlLatticeLoader();

        $path = $app['bisight.datamodelpath'] . '/lattice';
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


    public function viewLatticeAction(Application $app, Request $request, $dwcode, $latticecode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $storage = $dw->getStorage();

        $filename = $app['bisight.datamodelpath'] . '/lattice/' . $latticecode . '.xml';
        $loader = new XmlLatticeLoader();
        $lattice = $loader->loadFile($filename);
        $lattice->setName(str_replace('.xml', '', basename($filename)));

        $q = new LatticeQuery($lattice);
        foreach ($lattice->getColumns() as $column) {
            $q->addColumn($column);
        }
        
        $res = $storage->latticeQuery($q);
        //print_r($res);

        $html = $this->getResultSetHtml($res, 0, 2000);
        $data['tablehtml'] = $html;
        $data['lattice'] =  $lattice;
        $data['rowcount'] =  $res->getRowCount();
        return new Response($app['twig']->render(
            'lattice/view.html.twig',
            $data
        ));
    }

    public function downloadLatticeAction(Application $app, Request $request, $dwcode, $latticecode)
    {
        set_time_limit(0);
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $storage = $dw->getStorage();

        $filename = $app['bisight.datamodelpath'] . '/lattice/' . $latticecode . '.xml';
        $loader = new XmlLatticeLoader();
        $lattice = $loader->loadFile($filename);
        $lattice->setName(str_replace('.xml', '', basename($filename)));

        $q = new LatticeQuery($lattice);
        foreach ($lattice->getColumns() as $column) {
            $q->addColumn($column);
        }

        $res = $storage->latticeQuery($q);
        //print_r($res);
        $excel = $this->getResultSetExcel($res, 'Lattice ' . $latticecode);
        $format = $request->query->get('format');
        return $this->getExcelResponse($excel, $latticecode, $format);
    }

    private function getHtmlWidget(Parameter $parameter, $value)
    {
        switch ($parameter->getType()) {
            case 'date':
                $o = '<input ';
                $o .= ' required="required"';
                $o .= ' class="form-control"';
                $o .= ' type="date"';
                $o .= ' name="PARAMETER_' . $parameter->getName() . '"';

                $htmlvalue = substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2);

                $o .= ' value="' . $htmlvalue . '"/>';
                break;
            case 'select':
                $o = '<select ';
                $o .= ' required="required"';
                $o .= ' class="form-control"';
                $o .= ' name="PARAMETER_' . $parameter->getName() . '"';
                $o .= '>';
                foreach ($parameter->getOptions() as $option) {
                    $o .= '<option value="' . $option->getValue() . '"';
                    if ($value == $option->getValue()) {
                        $o .= ' selected="selected"';
                    }
                    $o .= '>' . $option->getLabel() . '</option>';
                }
                $o .= '</select>';

                break;
            default:
                throw new RuntimeException("Unsupported parameter type: ". $parameter->getType());
        }

        return $o;
    }

    public function viewLatticeReportAction(Application $app, Request $request, $dwcode, $reportname)
    {
        set_time_limit(0);
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $storage = $dw->getStorage();

        $filename = $app['bisight.datamodelpath'] . '/lattice-report/' . $reportname . '.xml';

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

            $html = $this->getHtmlWidget($parameter, $value);
            $htmlwidgets[$name] = $html;
            $values[$name] = $value;
        }

        $ds = $report->getLattice();

        $parameters = array();
        $q = $report->getQuery();

        $res = $storage->latticeQuery($q, $values);

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
            $excel = $this->getResultSetExcel($res, 'Report ' . $report->getName());
            return $this->getExcelResponse($excel, $report->getName(), $format);
        }

        $html = $this->getResultSetHtml($res);
        $data['tablehtml'] = $html;
        $data['report'] =  $report;
        $data['htmlwidgets'] = $htmlwidgets;
        return new Response($app['twig']->render(
            'latticereport/view.html.twig',
            $data
        ));
    }

    public function indexLatticeReportAction(Application $app, Request $request, $dwcode)
    {
        $dwrepo = $app->getDataWarehouseRepository();
        $dw = $dwrepo->getByCode($dwcode);
        $dbname = $dw->getConfig('dbname');
        $dm = new DatabaseManager();
        $pdo = $dm->getPdo($dbname);


        $latticeRepo = $app->getLatticeRepository();

        $loader = new XmlLatticeReportLoader($latticeRepo);

        $path = $app['bisight.datamodelpath'] . '/lattice-report';
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
