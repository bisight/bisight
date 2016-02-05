<?php
namespace BiSight\Portal\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use BiSight\Core\Driver\ResultSetInterface;
use BiSight\Core\Model\Parameter;
use BiSight\Core\Model\Column;
use RuntimeException;
use PDO;

class WarehouseController
{
    public function indexAction(Application $app, Request $request)
    {
        $data = array("name" => $app['app']['name']);
        $token = $app['security.token_storage']->getToken();
        $user = $token->getUser();
        $warehouseRepo = $app->getRepository('warehouse');

        $warehouses = array();
        foreach ($warehouseRepo->findAll() as $warehouse) {
            $warehouses[] = $warehouse;
        }
        $data['warehouses'] = $warehouses;

        return new Response($app['twig']->render(
            'warehouse/index.html.twig',
            $data
        ));
    }

    public function viewAction(Application $app, Request $request, $warehouseName)
    {
        $data = array();

        return new Response($app['twig']->render(
            'warehouse/view.html.twig',
            $data
        ));
    }


}
