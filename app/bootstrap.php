<?php

use BiSight\Portal\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Application();

$app->before(function (Request $request, Application $app) {
    $urlGenerator = $app['url_generator'];
    $urlGeneratorContext = $urlGenerator->getContext();
    
    $warehouseName = null;
    if ($request->attributes->has('warehouseName')) {
        $warehouseName = $warehouseName = $request->attributes->get('warehouseName');
    }
    if ($request->attributes->has('spaceName')) {
        $warehouseName = $warehouseName = $request->attributes->get('spaceName');
    }
    if ($warehouseName) {
        $accountName = $request->attributes->get('accountName');
        
        $repo = $app->getRepository('warehouse');
        $warehouse = $repo->findOneByAccountNameAndName($accountName, $warehouseName);
        $app['twig']->addGlobal('warehouse', $warehouse);
        $urlGeneratorContext->setParameter('warehouseName', $warehouse->getName());
        
        
        $token = $app['security.token_storage']->getToken();
        $user = $token->getUser();

        //--CHECK WAREHOISE PERMISSION --//
        if (!$oPermision = $app->getRepository('permission')->
        findOneOrNullBy(array('username' => $token->getUser()->getName(), 'warehouse_id' => $warehouse->getId()))) {
            return new Response($app['twig']->render('warehouse/access_denied.html.twig'), 403);
        }
    }
    //$app['request_context']->setBaseUrl($app['bisight.baseurl']);
});

return $app;
