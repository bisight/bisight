<?php

namespace BiSight\Portal;

use Silex\Application as SilexApplication;
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
use BiSight\Common\Model\DataWarehouse;
use BiSight\Engine\Repository\StaticSchemaRepository;
use BiSight\Engine\Repository\ArrayDataWarehouseRepository;

use RuntimeException;

class Application extends SilexApplication
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->configureParameters();
        $this->configureService();
        $this->configureRoutes();
        $this->configureTemplateEngine();
        $this->configureSecurity();
    }

    private function configureService()
    {
        // the form service
        /*
        $this->register(new TranslationServiceProvider(), array(
              'locale' => 'en',
              'translation.class_path' =>  __DIR__.'/../vendor/symfony/src',
              'translator.messages' => array(),
        ));
        $this->register(new FormServiceProvider());
        */
        $this->register(new RoutingServiceProvider());
        
        $dm = new DatabaseManager();
        //$this['bisight.portal.pdo'] = $dm->getPdo('bisight');
        $this['bisight.warehouse.pdo'] = $dm->getPdo('bi_l_objettrouve');

    }

    private $dataWarehouseRepository;
    private $schemaRepository;
    
    private function configureParameters()
    {
        $this['debug'] = true;
        $json = file_get_contents(__DIR__.'/../../config.json');
        $config = json_decode($json, true);
        $this['name'] = $config['name'];
        
        $this->dataWarehouseRepository = new ArrayDataWarehouseRepository($config['datawarehouses']);
        
        $this->schemaRepository = new StaticSchemaRepository();
        
        $this['bisight.repository.schema'] = $schemaRepository;
        
        $dataWarehouseRepository = new ArrayDataWarehouseRepository($config['datawarehouses']);
        
        $this['bisight.baseurl'] = 'test';
        
        
    }

    private function configureRoutes()
    {
        $locator = new FileLocator(array(__DIR__.'/../../app'));
        $loader = new YamlFileLoader($locator);
        $this['routes'] = $loader->load('routes.yml');
    }

    private function configureTemplateEngine()
    {
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => array(
                __DIR__.'/Resources/views/',
            ),
        ));
    }

    private function configureSecurity()
    {
        return;
        $this->register(new SilexSecurityServiceProvider(), array());

        if ($security['encoder']) {
            $this['security.encoder.digest'] = new PlaintextPasswordEncoder(true);
        }

        /*
        $this['security.firewalls'] = array(
            'default' => array(
                'stateless' => true,
                'pattern' => '^/',
                'http' => true,
                'users' => $this->getUserRepository(),
            ),
        );
        */
    }

    private function getUserSecurityProvider()
    {
        $dbmanager = new DatabaseManager();
        $pdo = $dbmanager->getPdo('bisight');
        return new \BiSight\Portal\Repository\PdoUserRepository($pdo);
    }

    /*
    public function getPdo($databaseName)
    {
        if (!$this->offsetExists('pdo.'.$databaseName)) {
            $dm = new DatabaseManager();
            $this['pdo.'.$databaseName] = $dm->getPdo($databaseName);
        }

        return $this['pdo.'.$databaseName];
    }
    */
    
    public function getDataWarehouseRepository()
    {
        return $this->dataWarehouseRepository;
    }
    
    public function getSchemaRepository()
    {
        return $this->schemaRepository;
    }
}
