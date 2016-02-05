<?php
namespace BiSight\Portal\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use RuntimeException;
use PDO;

class OlapController
{
    public function viewSchemaAction(Application $app, Request $request, $accountName, $warehouseName)
    {
        $warehouseRepo = $app->getRepository('warehouse');
        $warehouse = $warehouseRepo->findOneByAccountNameAndName($accountName, $warehouseName);

        $schemarepo = $app->getSchemaRepository();
        $schemaName = 'pennyblossom';
        $schema = $schemarepo->getByName($schemaName);

        $data = array('schema' => $schema);

        return new Response($app['twig']->render(
            'olap/schema.html.twig',
            $data
        ));
    }
}
