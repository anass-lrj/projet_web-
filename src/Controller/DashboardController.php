<?php
namespace App\Controller;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use Doctrine\ORM\EntityManager;
use App\Domain\User;
use Exception;
use Psr\Container\ContainerInterface;

class DashboardController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function dashboard(Request $request, Response $response, $args)
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'dashboard.html.twig');
    }

    public function handleLogin(Request $request, Response $response, $args)
    {
        
    }

    public function registerRoutes($app)
    {
        $app->get('/dashboard', [$this, 'dashboard'])->setName('dashboard');
    }
}
