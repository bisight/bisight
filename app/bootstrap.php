<?php

use BiSight\Portal\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

$app = new Application();

$app->before(function (Request $request, Application $app) {
    $urlGenerator = $app['url_generator'];
    $urlGeneratorContext = $urlGenerator->getContext();

    $token = $app['security.token_storage']->getToken();
    $user = $token->getUser();
    if ($user == 'anon.') {
        if ($request->get('_route') != 'login') {
            return new RedirectResponse(
                $app['url_generator']->generate('login')
            );
        }
    }
    
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
        $urlGeneratorContext->setParameter('spaceName', $warehouse->getName());
        

        //--CHECK WAREHOUSE PERMISSION --//
        if (!$oPermision = $app->getRepository('permission')->
        findOneOrNullBy(array('username' => $user->getName(), 'warehouse_id' => $warehouse->getId()))) {
            return new Response($app['twig']->render('warehouse/access_denied.html.twig'), 403);
        }
    }
    //$app['request_context']->setBaseUrl($app['bisight.baseurl']);
});

return $app;
