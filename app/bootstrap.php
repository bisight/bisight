<?php

use BiSight\Portal\Application;

use Symfony\Component\HttpFoundation\Request;

$app = new Application();

$app->before(function (Request $request, Application $app) {

    if ($request->attributes->has('dwcode')) {
        $dwcode = $request->attributes->get('dwcode');
        $repo = $app->getDataWarehouseRepository();
        $dw = $repo->getByCode($dwcode);
        $app['twig']->addGlobal('datawarehouse', $dw);
        
        
        $token = $app['security']->getToken();
        $user = $token->getUser();
        if (!$user->hasRole('ROLE_' . $dwcode)) {
            throw new RuntimeException("Access denied");
        }

    }
    $app['request_context']->setBaseUrl($app['bisight.baseurl']);
});

return $app;
