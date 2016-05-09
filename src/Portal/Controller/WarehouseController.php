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
        $permissionRepo = $app->getRepository('permission');

        $warehouses = array();
        foreach ($warehouseRepo->findAll() as $warehouse) {
            if (
                $permissionRepo->findOneOrNullBy(
                    array(
                        'username' => $token->getUser()->getName(),
                        'warehouse_id' => $warehouse->getId()
                    )
                )
            ) {
                $warehouses[] = $warehouse;
            }
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
        $pdo = $app->getWarehouseDriver($app['warehouse']);
        if (!$pdo) {
            return $app->redirect($app['url_generator']->generate('warehouse_admin'));
        }

        return new Response($app['twig']->render(
            'warehouse/view.html.twig',
            $data
        ));
    }
    
    public function adminAction(Application $app, Request $request, $accountName, $warehouseName)
    {
        $data = array();
        $accountName = $request->get('accountName');
        return  $this->getAdminForm($app, $request, $app['warehouse']->getId());
    }
    
    protected function getAdminForm(Application $app, Request $request, $id = null)
    {
        $accountName = $request->get('accountName');
        $warehouseRepo = $app->getRepository('warehouse');

        $entity = $warehouseRepo->find($id);
        //-- GENERATE FORM --//
        $form = $app['form.factory']->createBuilder('form', $entity)
        ->add('connection', 'text', array(
            'required' => false,
            'trim' => true,
            'attr' => array(
                'autofocus'  => '',
                'placeholder' => 'Connection string',
            )
        ))
        ->getForm();

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $data = $form->getData();

            if ($form->isValid()) {
                $entity->setConnection($data->connection);
                if ($warehouseRepo->persist($entity)) {
                    return $app->redirect($app['url_generator']->generate('warehouse_view'));
                }
            }
        }
        
        return new Response($app['twig']->render(
            'warehouse/admin.html.twig',
            array(
                'form' => $form->createView(),
                'entity' => $entity,
                'add' => false,
            )
        ));
    }
}
