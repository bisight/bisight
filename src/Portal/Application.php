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
use BiSight\Core\Driver\PdoDriver;
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
        $pdo = $dm->getPdo('bi_l_pommedejus');

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
}
