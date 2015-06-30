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
use BiSight\DataWarehouse\Model\DataWarehouse;
use BiSight\DataWarehouse\Repository\ArrayDataWarehouseRepository;
use BiSight\Olap\Repository\StaticSchemaRepository;
use BiSight\DataSet\Repository\XmlDataSetRepository;
use BiSight\DataSet\Loader\XmlLoader as XmlDataSetLoader;
use BiSight\DataSet\Loader\XmlReportLoader as XmlDataSetReportLoader;


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
    }

    private $dataWarehouseRepository;
    private $schemaRepository;

    private function configureParameters()
    {
        $this['debug'] = true;
        $json = file_get_contents(__DIR__.'/../../config.json');
        $config = json_decode($json, true);
        $this['name'] = $config['name'];

        $this['bisight.baseurl'] = $config['baseurl'];
        $this['bisight.datamodelpath'] = __DIR__ . '/../../' . $config['datamodelpath'];

        $this->dataWarehouseRepository = new ArrayDataWarehouseRepository($config['datawarehouses']);

        $this->schemaRepository = new StaticSchemaRepository();

        $loader = new XmlDataSetLoader();
        $this->dataSetRepository = new XmlDataSetRepository(
            $loader,
            $this['bisight.datamodelpath'] . '/dataset'
        );

        $this['config.security'] = $config['security'];
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
        $this->register(new SilexSecurityServiceProvider(), array());

        // $this['security.encoder.digest'] = new PlaintextPasswordEncoder(true);
        if (isset($this['config.security']['encoder'])) {
            $digest = '\\Symfony\\Component\\Security\\Core\\Encoder\\'.$this['config.security']['encoder'];
            $this['security.encoder.digest'] = new $digest(true);
        }

        $this['security.firewalls'] = array(
            'default' => array(
                'stateless' => true,
                'pattern' => '^/',
                'http' => true,
                'users' => $this->getUserSecurityProvider(),
            ),
        );
    }

    private function getUserSecurityProvider()
    {
        foreach ($this['config.security']['providers'] as $providerConfig) {
            switch ($providerConfig['name']) {
                case 'pdo':
                    $dbmanager = new DatabaseManager();
                    return new \BiSight\Portal\Repository\PdoUserRepository(
                        $dbmanager->getPdo($providerConfig['database'])
                    );
                case 'userbase':
                    return new \UserBase\Client\UserProvider(
                        new \UserBase\Client\Client(
                            $providerConfig['url'],
                            $providerConfig['username'],
                            $providerConfig['password']
                        )
                    );
                default:
                    break;
            }
        }
        throw new RuntimeException('Cannot find any security provider');
    }

    // private function getUserRepository()
    // {
    //     $dbmanager = new DatabaseManager();
    //     $pdo = $dbmanager->getPdo('bisight');
    //     return new \BiSight\Portal\Repository\PdoUserRepository($pdo);
    // }

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

    public function getDataSetRepository()
    {
        return $this->dataSetRepository;
    }
}
