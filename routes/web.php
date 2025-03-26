<?php

use Slim\App;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {

    // Page d'accueil
    $app->get('/', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig');
    });
    // Page User
    $app->get('/user', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'user.twig', ['username' => 'JohnDoe']);
    });
    // Page Entreprise
    $app->get('/entreprise', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'entreprise.twig', );
    });
    // Page Contact 
    $app->get('/contact', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'contact.twig', );
    });
    // Page Offre
    $app->get('/offre', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'offre.twig', );
    });
    // Page CGU
    $app->get('/CGU', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'CGU.twig', );
    });
    // Page Mentions-légales
    $app->get('/Mentions-légales', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'Mentions-légales.twig', );
    });
};
