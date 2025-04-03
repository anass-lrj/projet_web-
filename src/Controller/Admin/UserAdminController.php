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
        $group->get('/list', UserAdminController::class . ':list')->setName('user-list');
        $group->get('/edit/{idUser}', UserAdminController::class . ':edit')->setName('user-edit');
        $group->post('/edit/{idUser}', UserAdminController::class . ':edit')->setName('user-edit');
        $group->get('/search', UserAdminController::class . ':search')->setName('user-search');
        $group->get('/add', UserAdminController::class . ':edit')->setName('user-add');
        $group->post('/add', UserAdminController::class . ':edit')->setName('user-add');
        $group->get('/delete/{idUser}', UserAdminController::class . ':delete')->setName('user-delete');
    })->add(AdminMiddleware::class) // Vérifie si c'est un admin
    ->add(PiloteMiddleware::class) // Laisse passer les pilotes
    ->add(UserMiddleware::class); // Vérifie si l'utilisateur est connecté
  
   }

   public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
       $add = true;
       $em = $this->container->get(EntityManager::class);
       $session = $this->container->get('session');
       $currentUser = $session->get('user'); 
   
       if (!$currentUser || !in_array($currentUser->getRole(), ['admin', 'pilote'])) {
           $routeParser = RouteContext::fromRequest($request)->getRouteParser();
           $url = $routeParser->urlFor('login');
           return $response->withHeader('Location', $url)->withStatus(302);
       }
   
       if (isset($args['idUser'])) {
           $add = false;
           $user = $em->getRepository(User::class)->find($args['idUser']);
       } else {
           $user = new User('', '', new \DateTime(), '', '', '');
       }
   
       // Vérification que le pilote ne peut pas modifier les comptes admin ou pilote
       if ($currentUser->getRole() === 'pilote' && in_array($user->getRole(), ['admin', 'pilote'])) {
           $response->getBody()->write("Un pilote ne peut pas modifier un compte admin ou pilote.");
           return $response->withStatus(403); // Forbidden
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
   
           // Vérification des permissions lors de la modification
           if ($currentUser->getRole() === 'pilote' && in_array($role, ['admin', 'pilote'])) {
               $response->getBody()->write("Un pilote ne peut pas créer un compte admin ou un autre pilote.");
               return $response->withStatus(403);
           }
   
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
   
           // Redirection vers la liste des utilisateurs après l'enregistrement
           $routeParser = RouteContext::fromRequest($request)->getRouteParser();
           $url = $routeParser->urlFor('list-racine');  // redirection vers la liste des utilisateurs
           return $response->withHeader('Location', $url)->withStatus(302);
       }
   
       $promotions = $em->getRepository(Promotion::class)->findAll();
       $view = Twig::fromRequest($request);
   
       return $view->render($response, 'Admin/User/user-edit.html.twig', [
           'userEntity' => $user,
           'add' => $add,
           'promotions' => $promotions
       ]);
   }
   


public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $em = $this->container->get(EntityManager::class);
    $session = $this->container->get('session');
    $currentUser = $session->get('user');
    $user = $em->getRepository(User::class)->find($args['idUser']);

    // Vérification des permissions de suppression
    if (!$user) {
        $response->getBody()->write("Utilisateur non trouvé !");
        return $response->withStatus(404);
    }

    // Un pilote ne peut pas supprimer un compte admin ou pilote
    if ($currentUser->getRole() === 'pilote' && in_array($user->getRole(), ['admin', 'pilote'])) {
        $response->getBody()->write("Un pilote ne peut pas supprimer un compte admin ou pilote.");
        return $response->withStatus(403); // Forbidden
    }

    if ($user) {
        $em->remove($user);
        $em->flush();
    }

    // Redirection après suppression
    $routeParser = RouteContext::fromRequest($request)->getRouteParser();
    $url = $routeParser->urlFor('list-racine');
    return $response->withHeader('Location', $url)->withStatus(302);
}


public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $em = $this->container->get(EntityManager::class);
    $queryParams = $request->getQueryParams();
    $role = $queryParams['role'] ?? null;

    $queryBuilder = $em->getRepository(User::class)->createQueryBuilder('u');

    if (!empty($role)) {
        $queryBuilder->where('u.role = :role')
                     ->setParameter('role', $role);
    }

    $users = $queryBuilder->getQuery()->getResult();

    $view = Twig::fromRequest($request);

    return $view->render($response, 'Admin/User/user-list.html.twig', [
        'users' => $users,
        'selectedRole' => $role, // Permet de garder la sélection active dans le formulaire
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

   public function search(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $em = $this->container->get(EntityManager::class);
    $queryParams = $request->getQueryParams();
    $prenom = $queryParams['prenom'] ?? null;
    $nom = $queryParams['nom'] ?? null;

    $queryBuilder = $em->getRepository(User::class)->createQueryBuilder('u');

    if ($prenom) {
        $queryBuilder->andWhere('u.prenom LIKE :prenom')
                     ->setParameter('prenom', '%' . $prenom . '%');
    }

    if ($nom) {
        $queryBuilder->andWhere('u.nom LIKE :nom')
                     ->setParameter('nom', '%' . $nom . '%');
    }

    $users = $queryBuilder->getQuery()->getResult();

    $view = Twig::fromRequest($request);

    return $view->render($response, 'Admin/User/user-list.html.twig', [
        'users' => $users,
        'prenom' => $prenom,
        'nom' => $nom,
    ]);
}

}