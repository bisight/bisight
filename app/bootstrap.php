<?php

use BiSight\Portal\Application;

use Symfony\Component\HttpFoundation\Request;

$app = new Application();

$app->before(function (Request $request, Application $app) {
    $urlGenerator = $app['url_generator'];
    $urlGeneratorContext = $urlGenerator->getContext();
    if ($request->attributes->has('warehouseName')) {
        $accountName = $request->attributes->get('accountName');
        $warehouseName = $request->attributes->get('warehouseName');
        $repo = $app->getRepository('warehouse');
        $warehouse = $repo->findOneByAccountNameAndName($accountName, $warehouseName);
        $app['twig']->addGlobal('warehouse', $warehouse);
        $urlGeneratorContext->setParameter('warehouseName', $warehouse->getName());
        
        
        $token = $app['security.token_storage']->getToken();
        $user = $token->getUser();
        //if (!$user->hasRole('ROLE_' . $dwcode)) {
            //throw new RuntimeException("Access denied");
        //}

    }
    //$app['request_context']->setBaseUrl($app['bisight.baseurl']);
});

return $app;
