<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\UserMiddleware;


class EntrepriseController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/entreprises', EntrepriseController::class . ':Entreprise');
        
        $app->get('/entreprises/edit', EntrepriseController::class . ':editEntreprise')
        ->setName('entreprise-edit');
  
    
        $app->get('/entreprises/list', EntrepriseController::class . ':Entreprise')
            ->setName('entreprises-list');
    
        $app->get('/entreprises/list/page/{page}', EntrepriseController::class . ':paginatedList')
            ->setName('paginatedList');
    
        $app->get('/entreprises/edit/{idUser}', EntrepriseController::class . ':editEntreprise')
            ->setName('entreprises-edit');
    
        $app->post('/entreprises/edit/{idUser}', EntrepriseController::class . ':editEntreprise')
            ->setName('entreprises-edit');
    
        $app->get('/entreprises/add', EntrepriseController::class . ':addEntreprise')
            ->setName('entreprises-add');
    
        $app->post('/entreprises/add', EntrepriseController::class . ':addEntreprise')
            ->setName('entreprises-add');
    
        $app->get('/entreprises/delete/{idUser}', EntrepriseController::class . ':delete')
            ->setName('entreprises-delete');
    }

    public function Entreprise(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'Admin/User/entreprise-list.html.twig', []);
    }

    public function editEntreprise(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    

    // Rendu de la vue
    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/entreprise-edit.html.twig', [
        
    ]);
}
public function addEntreprise(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $view = Twig::fromRequest($request);
        return $view->render($response, 'Admin/User/entreprise-edit.html.twig', []);
}
}



