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
    
        $app->get('/entreprises/list', EntrepriseController::class . ':paginatedList')
            ->setName('list-racine');
    
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
    $add = true;
    $em = $this->container->get(EntityManager::class);
    
    if (isset($args['id'])) {
        $add = false;
        $entreprise = $em->getRepository(Entreprise::class)->find($args['id']);
    } else {
        $entreprise = new Entreprise('', ''); // Initialisation avec un titre et un email vides
    }

    if ($request->getMethod() == 'POST') {
        // Récupérer les données du formulaire
        $titre = $request->getParsedBody()['titre'] ?? null;
        $email = $request->getParsedBody()['email'] ?? null;
        $ville = $request->getParsedBody()['ville'] ?? null;
        $description = $request->getParsedBody()['description'] ?? null;
        $contactTelephone = $request->getParsedBody()['contactTelephone'] ?? null;
        $nombreStagiaires = $request->getParsedBody()['nombreStagiaires'] ?? null;
        $evaluationMoyenne = $request->getParsedBody()['evaluationMoyenne'] ?? null;

        // Mise à jour des propriétés de l'entreprise
        $entreprise->setTitre($titre);
        $entreprise->setEmail($email);
        $entreprise->setVille($ville);
        $entreprise->setDescription($description);
        $entreprise->setContactTelephone($contactTelephone);
        $entreprise->setNombreStagiaires($nombreStagiaires ? (int)$nombreStagiaires : null);
        $entreprise->setEvaluationMoyenne($evaluationMoyenne ? (float)$evaluationMoyenne : null);

        // Sauvegarde en base de données
        $em->persist($entreprise);
        $em->flush();

        // Redirection après sauvegarde
        if (!$add) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('entreprises-edit', ['id' => $entreprise->getId()]);
            $response = $this->container->get(ResponseFactoryInterface::class)->createResponse();
            return $response->withHeader('Location', $url)->withStatus(302);
        }
    }

    // Rendu de la vue
    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/entreprise-edit.html.twig', [
        'entreprise' => $entreprise,
        'add' => $add
    ]);
}
public function addEntreprise(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/entreprise-edit.html.twig', []);
}
}



