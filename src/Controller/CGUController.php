<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\UserMiddleware;


class CGUController
{
   private $container;

   // constructor receives container instance
   public function __construct(ContainerInterface $container)
   {
       $this->container = $container;
   }


   public function registerRoutes($app)
   {
       $app->get('/cgu', \App\Controller\CGUController::class . ':cgu')->add(UserMiddleware::class);
   }


   public function cgu(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
        $view = Twig::fromRequest($request);
        
        return $view->render($response, 'CGU.html.twig', []);
   }

}