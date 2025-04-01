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
           $user = new User('', '', new \DateTime(), '', '', '');
       }
   
       if ($request->getMethod() == 'POST') {
           $prenom = $request->getParsedBody()['prenom'];
           $nom = $request->getParsedBody()['nom'];
           $email = $request->getParsedBody()['email'];
           $motDePasse = $request->getParsedBody()['motDePasse'] ?? null;
           $role = $request->getParsedBody()['role'];
           $dateNaissance = isset($request->getParsedBody()['dateNaissance']) 
               ? \DateTime::createFromFormat('Y-m-d', $request->getParsedBody()['dateNaissance']) 
               : null;
   
           // Vérification des permissions
<<<<<<< HEAD
           if ($currentUser->getRole() === 'pilote' && $role === 'admin') {
               return $response->withStatus(403)->write("Un pilote ne peut pas créer un compte admin.");
           }
   
=======
           if ($currentUser->getRole() === 'pilote' && in_array($role, ['admin', 'pilote'])) {
            $response->getBody()->write("Un pilote ne peut pas créer un compte admin ou un autre pilote.");
            return $response->withStatus(403);
        }
        
>>>>>>> a0a002468d47fe283950e6882da04d497714e778
           // Association des promotions si user ou pilote
           if (in_array($role, ['user', 'pilote'])) {
               $promotionIds = $request->getParsedBody()['promotions'] ?? [];
               $promotions = $em->getRepository(Promotion::class)->findBy(['id' => $promotionIds]);
               foreach ($promotions as $promotion) {
                   $user->addPromotion($promotion);
               }
           }
   
           $user->setPrenom($prenom);
           $user->setNom($nom);
           $user->setEmail($email);
           if (!empty($motDePasse)) {
               $user->setMotDePasse($motDePasse);
           }
           $user->setRole($role);
           if ($dateNaissance !== null) {
               $user->setDateNaissance($dateNaissance);
           }
   
           $em->persist($user);
           $em->flush();
   
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
