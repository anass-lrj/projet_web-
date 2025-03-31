<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use App\Domain\Entreprise;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseFactoryInterface;
use Doctrine\ORM\EntityManager;

class EntrepriseController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/entreprises', EntrepriseController::class . ':listEntreprises')->setName('entreprises-list');
        $app->get('/entreprises/edit/{id}', EntrepriseController::class . ':editEntreprise')->setName('entreprise-edit');
        $app->post('/entreprises/edit/{id}', EntrepriseController::class . ':editEntreprise');
        $app->get('/entreprises/add', EntrepriseController::class . ':editEntreprise')->setName('entreprises-add');
        $app->post('/entreprises/add', EntrepriseController::class . ':editEntreprise');
        $app->get('/entreprises/delete/{id}', EntrepriseController::class . ':delete')->setName('entreprise-delete');
    }


    public function editEntreprise(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $entityManager = $this->container->get(EntityManager::class);
        $add = !isset($args['id']);

        if ($add) {
            $entreprise = new Entreprise();
        } else {
            $entreprise = $entityManager->getRepository(Entreprise::class)->find($args['id']);

            if (!$entreprise) {
                $response->getBody()->write("Entreprise non trouvée !");
                return $response->withStatus(404);
            }
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $entreprise->setTitre($data['titre'] ?? '');
            $entreprise->setEmail($data['email'] ?? '');
            $entreprise->setVille($data['ville'] ?? null);
            $entreprise->setDescription($data['description'] ?? null);
            $entreprise->setContactTelephone($data['contactTelephone'] ?? null);
            $entreprise->setNombreStagiaires(isset($data['nombreStagiaires']) ? (int) $data['nombreStagiaires'] : null);
            $entreprise->setEvaluationMoyenne(isset($data['evaluationMoyenne']) ? (float) $data['evaluationMoyenne'] : null);

            $entityManager->persist($entreprise);
            $entityManager->flush();

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('entreprises-list');
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        $view = Twig::fromRequest($request);
        return $view->render($response, 'Admin/User/entreprise-edit.html.twig', [
            'entrepriseEntity' => $entreprise,
            'add' => $add,
        ]);
    }

    private function getEntrepriseById($id): ?Entreprise
    {
        return new Entreprise();
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $em = $this->container->get(EntityManager::class);
        $entreprise = $em->getRepository(Entreprise::class)->find($args['id']);
    
        if ($entreprise) {
            $em->remove($entreprise);
            $em->flush();
        }
    
        // Redirection vers la liste des entreprises après suppression
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('entreprises-list');
        return $response->withHeader('Location', $url)->withStatus(302);
    }
    

    public function paginatedList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $em = $this->container->get(EntityManager::class);
        $page = (int)($args['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $entreprises = $em->getRepository(Entreprise::class)->findBy([], null, $limit, $offset);
        $totalEntreprises = $em->getRepository(Entreprise::class)->count([]);

        $view = Twig::fromRequest($request);

        return $view->render($response, 'Admin/User/entreprise-list.html.twig', [
            'entreprises' => $entreprises,
            'currentPage' => $page,
            'totalPages' => ceil($totalEntreprises / $limit),
        ]);
    }
    public function listEntreprises(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $em = $this->container->get(EntityManager::class);
        $entreprises = $em->getRepository(Entreprise::class)->findAll();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'Admin/User/entreprise-list.html.twig', [
            'entreprises' => $entreprises,
        ]);
    }
   
}

