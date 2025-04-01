<?php
namespace App\Controller;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use Doctrine\ORM\EntityManager;
use App\Domain\User;
use Exception;
use Psr\Container\ContainerInterface;

class LogoutController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function logout(Request $request, Response $response, $args)
    {
        $this->container->get('session')->clear();

        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    public function registerRoutes($app)
    {
        $app->get('/logout', [$this, 'logout'])->setName('logout');
    }
}