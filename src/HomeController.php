<?php

namespace App;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class HomeController
{
   private $container;

   // constructor receives container instance
   public function __construct(ContainerInterface $container)
   {
       $this->container = $container;
   }

   public function home(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
        $view = Twig::fromRequest($request);
        
        return $view->render($response, 'home.html.twig', [
            'name' => 'John',
        ]);
   }

}