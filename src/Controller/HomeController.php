<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\UserMiddleware;


class HomeController
{
   private $container;

   // constructor receives container instance
   public function __construct(ContainerInterface $container)
   {
       $this->container = $container;
   }


   public function registerRoutes($app)
   {
       $app->get('/login', \App\Controller\HomeController::class . ':login')->setName('login');
       $app->get('/', \App\Controller\HomeController::class . ':home')->add(UserMiddleware::class);
       $app->get('/admin', \App\Controller\HomeController::class . ':home')->add(AdminMiddleware::class);
   }


   public function home(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
        $view = Twig::fromRequest($request);
        
        return $view->render($response, 'home.html.twig', [
            'name' => 'John',
            'test' => $request->getAttribute('user'),
            'session' => $this->container->get('session')
        ]);
   }

   public function login(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
        $view = Twig::fromRequest($request);

        //FAKE LOGIN
        $this->container->get('session')->set('role', 'admin');
        $this->container->get('session')->set('idUser', 1);
        
        return $view->render($response, 'home.html.twig', [
            'name' => 'John',
        ]);
   }

}