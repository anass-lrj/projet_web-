<?php

namespace App\Controller\Admin;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\UserMiddleware;
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
            $group->get('/list', UserAdminController::class . ':paginatedList')
                ->setName('list-racine');

            $group->get('/list/page/{page}', UserAdminController::class . ':paginatedList')
                ->setName('paginatedList');

            $group->get('/edit/{idUser}', UserAdminController::class . ':edit')
                ->setName('user-edit');

            $group->post('/edit/{idUser}', UserAdminController::class . ':edit')
                ->setName('user-edit');

            $group->get('/add', UserAdminController::class . ':edit')
                ->setName('user-add');

            $group->post('/add', UserAdminController::class . ':edit')
                ->setName('user-add');

            $group->get('/delete/{idUser}', UserAdminController::class . ':delete')
                ->setName('user-delete');


        })->add(AdminMiddleware::class)
        ->add(UserMiddleware::class);
   }

   public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $add = true;
    $em = $this->container->get(EntityManager::class);
    
    if (isset($args['idUser'])) {
        $add = false;
        $user = $em->getRepository(User::class)->find($args['idUser']);
    } else {
        $user = new User('', '', new \DateTime(), '', '', '');
    }

    if ($request->getMethod() == 'POST') {
        // Récupérer les données du formulaire
        $prenom = $request->getParsedBody()['prenom'];
        $nom = $request->getParsedBody()['nom'];
        $email = $request->getParsedBody()['email'];
        $motDePasse = isset($request->getParsedBody()['motDePasse']) ? $request->getParsedBody()['motDePasse'] : null;
        $role = $request->getParsedBody()['role'];
        
        // Vérifier si 'dateNaissance' existe dans le formulaire
        $dateNaissance = isset($request->getParsedBody()['dateNaissance']) 
            ? \DateTime::createFromFormat('Y-m-d', $request->getParsedBody()['dateNaissance']) 
            : null; // Utiliser null ou une valeur par défaut si la clé 'dateNaissance' est manquante

        // Mise à jour des propriétés de l'utilisateur
        $user->setPrenom($prenom);
        $user->setNom($nom);
        $user->setEmail($email);

        // Ne mettre à jour le mot de passe que si un nouveau mot de passe est fourni
        if (!empty($motDePasse)) {
            $user->setMotDePasse($motDePasse);
        }
        $user->setRole($role);
        if ($dateNaissance !== null) {
            $user->setDateNaissance($dateNaissance);
        }

        $em->persist($user);
        $em->flush();

        if (!$add) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('user-edit', ['idUser' => $user->getId()]);
            $response = $this->container->get(ResponseFactoryInterface::class)->createResponse();
            return $response->withHeader('Location', $url)->withStatus(302);
        }
    }

    $view = Twig::fromRequest($request);

    return $view->render($response, 'Admin/User/user-edit.html.twig', [
        'userEntity' => $user,
        'add' => $add
    ]);
}

   public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
        $em = $this->container->get(EntityManager::class);
        $user = $em->getRepository(User::class)->find($args['idUser']);

        if ($user) {
            $em->remove($user);
            $em->flush();
        }

        // Redirect to user list page after deletion
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('list-racine');
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
