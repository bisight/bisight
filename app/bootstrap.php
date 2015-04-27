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
    }
    $app['request_context']->setBaseUrl($app['bisight.baseurl']);
});

return $app;
