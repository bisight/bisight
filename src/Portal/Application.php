<?php

namespace BiSight\Portal;

use Radvance\Framework\BaseWebApplication;
use Radvance\Framework\FrameworkApplicationInterface;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SecurityServiceProvider as SilexSecurityServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\RoutingServiceProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use BiSight\Core\Model\Parameter;
use BiSight\Core\Model\Option;
use BiSight\Table\Model\Table;
use BiSight\Core\Model\Column;
use BiSight\Table\QueryLoader\XmlQueryLoader;
use BiSight\Core\Driver\PdoDriver;
use BiSight\Core\TableLoader\XmlTableLoader;
use BiSight\Portal\Model\Warehouse;
use BiSight\DataWarehouse\Model\DataWarehouse;
use BiSight\DataWarehouse\Repository\ArrayDataWarehouseRepository;
use BiSight\Olap\Repository\StaticSchemaRepository;
use BiSight\Lattice\Repository\XmlLatticeRepository;
use BiSight\Lattice\Loader\XmlLoader as XmlLatticeLoader;
use BiSight\Lattice\Loader\XmlReportLoader as XmlLatticeReportLoader;

use RuntimeException;

class Application extends BaseWebApplication implements FrameworkApplicationInterface
{

    protected function configureService()
    {
        parent::configureService();
        
        $loader = new XmlLatticeLoader();
        $this->latticeRepository = new XmlLatticeRepository(
            $loader,
            $this['bisight']['datamodel_path'] . '/lattice'
        );
        $this->schemaRepository = new StaticSchemaRepository();
    }

    private $schemaRepository;
    private $latticeRepository;
    
    public function getSchemaRepository()
    {
        return $this->schemaRepository;
    }
    
    public function getLatticeRepository()
    {
        return $this->latticeRepository;
    }
    
    public function getWarehouseDriver(Warehouse $warehouse)
    {
        $dm = new DatabaseManager();
        $connection = $warehouse->getConnection();
        if (!$connection) {
            throw new RuntimeException("Connection not configured for " . $warehouse->getAccountName() . '/' . $warehouse->getName());
        }
        $pdo = $dm->getPdo($connection);

        $driver = new PdoDriver($pdo);
        return $driver;
    }
    
    public function getWarehouseDataModelPath(Warehouse $warehouse)
    {
        return $this['bisight']['datamodel_path'];
    }
    
    protected function getRepositoryPath()
    {
        return sprintf('%s/src/Portal/Repository', $this->getRootPath());
    }
    
    public function getTable(Warehouse $warehouse, $tableName)
    {
        $path = $this->getWarehouseDataModelPath($warehouse) . '/table';

        $loader = new XmlTableLoader();
        $filename = $path . '/' . $tableName . '.xml';
        if (file_exists($filename)) {
            $table = $loader->loadFile($tableName, $filename);
        } else {
            $table = new Table($tableName);
        }
        $driver = $this->getWarehouseDriver($warehouse);
        $columns = $driver->getColumns($tableName);
        foreach ($columns as $column) {
            if (!$table->hasColumn($column->getName())) {
                $table->setColumn($column);
            }
        }
        return $table;
    }
    
    public function getWarehouseQueries(Warehouse $warehouse)
    {
        $queries = [];
        $loader = new XmlQueryLoader();
        $path = $this->getWarehouseDataModelPath($warehouse) . '/query';
        $files = glob($path . '/*.xml');
        foreach ($files as $filename) {
            $queries[] = $loader->loadFile($filename, $path . '/../table');
        }
        return $queries;
    }
    
    public function getHtmlWidget(Parameter $parameter, $value)
    {
        switch ($parameter->getType()) {
            case 'text':
                $o = '<input ';
                //$o .= ' required="required"';
                $o .= ' class="form-control"';
                $o .= ' type="text"';
                $o .= ' name="PARAMETER_' . $parameter->getName() . '"';

                $htmlvalue = $value;

                $o .= ' value="' . $htmlvalue . '"/>';
                break;
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
}
