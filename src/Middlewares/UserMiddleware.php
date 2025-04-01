<?php
 
 namespace App\Middlewares;
 
 use Psr\Container\ContainerInterface;
 use Psr\Http\Message\ResponseInterface as Response;
 use Psr\Http\Message\ServerRequestInterface as Request;
 use Psr\Http\Server\MiddlewareInterface;
 use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
 use Slim\Routing\RouteContext;
 use Psr\Http\Message\ResponseFactoryInterface;
 use Doctrine\ORM\EntityManager;
 use App\Domain\User;
 
 class UserMiddleware implements MiddlewareInterface
 {
     private $container;
 
     public function __construct(ContainerInterface $container)
     {
         $this->container = $container;
     }
 
     public function process(Request $request, RequestHandler $handler): Response
     {
        $userSession = $this->container->get('session')->get('user');

        if ($userSession === null) {
             $routeParser = RouteContext::fromRequest($request)->getRouteParser();
             $url = $routeParser->urlFor('login');
             $response = $this->container->get(ResponseFactoryInterface::class)->createResponse();
             return $response
                 ->withHeader('Location', $url)
                 ->withStatus(302);
         }
     
         if ($userSession->getRole() === null) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('login');
            $response = $this->container->get(ResponseFactoryInterface::class)->createResponse();
            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        $em = $this->container->get(EntityManager::class);
        $user = $em->getRepository(User::class)->find($userSession->getId());
     
         if ($user === null) {
             $routeParser = RouteContext::fromRequest($request)->getRouteParser();
             $url = $routeParser->urlFor('login');
             $response = $this->container->get(ResponseFactoryInterface::class)->createResponse();
             return $response
                 ->withHeader('Location', $url)
                 ->withStatus(302);
         }
     
         $this->container->get('view')->getEnvironment()->addGlobal('currentUser', $user);
     
         $request = $request->withAttribute('user', $user);
     
         $response = $handler->handle($request);
         return $response;
     }
 }