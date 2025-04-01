<?php

namespace App\Controller\Admin;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\PiloteMiddleware;
use App\Middlewares\UserMiddleware;
use App\Domain\Promotion;
use Doctrine\ORM\EntityManager;
use App\Domain\User;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Routing\RouteCollectorProxy;

class UserAdminController
{
   private $container;

   public function __construct(ContainerInterface $container)
   {
       $this->container = $container;
   }

   public function registerRoutes($app)
   {
    $app->group('/admin/user', function (RouteCollectorProxy $group) {
        $group->get('/list', UserAdminController::class . ':paginatedList')->setName('list-racine');
        $group->get('/edit/{idUser}', UserAdminController::class . ':edit')->setName('user-edit');
        $group->post('/edit/{idUser}', UserAdminController::class . ':edit')->setName('user-edit');
        $group->get('/add', UserAdminController::class . ':edit')->setName('user-add');
        $group->post('/add', UserAdminController::class . ':edit')->setName('user-add');
        $group->get('/delete/{idUser}', UserAdminController::class . ':delete')->setName('user-delete');
    })->add(AdminMiddleware::class) // Vérifie si c'est un admin
    ->add(PiloteMiddleware::class) // Laisse passer les pilotes
    ->add(UserMiddleware::class); // Vérifie si l'utilisateur est connecté
  
   }

   public function editEntreprise(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
       $entityManager = $this->container->get(EntityManager::class);
       $session = $this->container->get('session');
       $currentUser = $session->get('user'); 
   
       // 🔒 Vérification du rôle
       if (!$currentUser || !in_array($currentUser->getRole(), ['admin', 'pilote'])) {
           $response->getBody()->write("Accès refusé.");
           return $response->withStatus(403);
       }
   
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
   
   public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
       $em = $this->container->get(EntityManager::class);
       $session = $this->container->get('session');
       $currentUser = $session->get('user');
   
       // 🔒 Seuls les admins et pilotes peuvent supprimer
       if (!$currentUser || !in_array($currentUser->getRole(), ['admin', 'pilote'])) {
           $response->getBody()->write("Accès refusé.");
           return $response->withStatus(403);
       }
   
       $entreprise = $em->getRepository(Entreprise::class)->find($args['id']);
   
       if ($entreprise) {
           $em->remove($entreprise);
           $em->flush();
       }
   
       // Redirection après suppression
       $routeParser = RouteContext::fromRequest($request)->getRouteParser();
       $url = $routeParser->urlFor('entreprises-list');
       return $response->withHeader('Location', $url)->withStatus(302);
   }
   

   public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
        $em = $this->container->get(EntityManager::class);
        $users = $em->getRepository(User::class)->findAll();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'Admin/User/user-list.html.twig', [
            'users' => $users,
        ]);
   }

   public function paginatedList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
        $em = $this->container->get(EntityManager::class);
        $page = (int)($args['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $users = $em->getRepository(User::class)->findBy([], null, $limit, $offset);
        $totalUsers = $em->getRepository(User::class)->count([]);

        $view = Twig::fromRequest($request);

        return $view->render($response, 'Admin/User/user-list.html.twig', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => ceil($totalUsers / $limit),
        ]);
   }
}
