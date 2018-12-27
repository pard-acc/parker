<?php

use \Psr\Http\Message\{ServerRequestInterface as RequestI, ResponseInterface as ResponseI};
use \Samas\PHP7\Kit\{AppKit, WebKit};
use \AntMan\Controller;

// Routes
$controller = $controller ?? null;

$app->group('/product', function () use ($app, $controller) {
    $controller = $controller ?? new \AntMan\Controller\ProductController();
    $app->post('/info/{pid}', function (RequestI $request, ResponseI $response, array $args) use ($controller) {
        return $controller->action("getProductInfo", $request, $response, $args);
    });
    $app->post('/list', function (RequestI $request, ResponseI $response, array $args) use ($controller) {
        return $controller->action("getProductList", $request, $response, $args);
    });
});

$app->group('/store', function () use ($app, $controller) {
    $controller = $controller ?? new \AntMan\Controller\StoreController();
    $app->post('/info/{sid}', function (RequestI $request, ResponseI $response, array $args) use ($controller) {
        return $controller->action("getStoreInfo", $request, $response, $args);
    });
});
