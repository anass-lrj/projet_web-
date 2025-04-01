<?php

namespace App\Middlewares;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseFactoryInterface;

class PiloteMiddleware implements MiddlewareInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $session = $this->container->get('session');
        $user = $session->get('user');

        if (!$user || !in_array($user->getRole(), ['admin', 'pilote'])) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('login');
            return $this->container->get(ResponseFactoryInterface::class)->createResponse()
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        return $handler->handle($request);
    }
}
