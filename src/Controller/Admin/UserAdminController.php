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

class UserAdminController
{
   private $container;

   // constructor receives container instance
   public function __construct(ContainerInterface $container)
   {
       $this->container = $container;
   }

   public function registerRoutes($app)
   {
       $app->get('/admin/user/list', UserAdminController::class . ':paginatedList')
        ->setName('list-racine')
        ->add(AdminMiddleware::class);

       $app->get('/admin/user/list/page/{page}', UserAdminController::class . ':paginatedList')
        ->setName('paginatedList')
        ->add(AdminMiddleware::class);

        
        $app->get('/admin/user/edit/{idUser}', UserAdminController::class . ':edit')
        ->setName('user-edit')
        ->add(AdminMiddleware::class);

        $app->get('/admin/user/add', UserAdminController::class . ':edit')
        ->setName('user-add')
        ->add(AdminMiddleware::class);
        
        $app->post('/admin/user/add', UserAdminController::class . ':edit')
        ->setName('user-add')
        ->add(AdminMiddleware::class);
   }

   public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
   {
        $add = true;
        $em = $this->container->get(EntityManager::class);

        if(isset($args['idUser'])){
            $add = false;
            $user = $em->getRepository(User::class)->find($args['idUser']);
        }else{
            $user = new User('');
        }
        
        if($request->getMethod() == 'POST'){
            if(isset($args['idUser'])){
                $user->setEmail($request->getParsedBody()['email']);
                $em->persist($user);
                $em->flush();
            } else {
                $user = new User('');
                $user->setEmail($request->getParsedBody()['email']);
                $em->persist($user);
                $em->flush();

                //redirect to edit
                $routeParser = RouteContext::fromRequest($request)->getRouteParser();
                $url = $routeParser->urlFor('user-edit', ['idUser' => $user->getId()]);
                $response = $this->container->get(ResponseFactoryInterface::class)->createResponse();
                return $response
                    ->withHeader('Location', $url)
                    ->withStatus(302);
            }
        }

        $view = Twig::fromRequest($request);
        
        return $view->render($response, 'Admin/User/user-edit.html.twig', [
            'userEntity' => $user,
            'add'=> $add
        ]);
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