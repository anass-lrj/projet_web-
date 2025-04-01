<?php
namespace App\Controller;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use Doctrine\ORM\EntityManager;
use App\Domain\Domaine;
use Psr\Container\ContainerInterface;
use App\Middlewares\UserMiddleware;
use Slim\Routing\RouteContext;

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

    public function listDomaines(Request $request, Response $response, $args)
    {
        $entityManager = $this->container->get(EntityManager::class);
        $domaines = $entityManager->getRepository(Domaine::class)->findAll();

        $view = Twig::fromRequest($request);
        return $view->render($response, 'Admin/User/domaine-list.html.twig', [
            'domaines' => $domaines
        ]);
    }

    public function addDomaine(Request $request, Response $response, $args)
    {
        $entityManager = $this->container->get(EntityManager::class);
        $data = $request->getParsedBody();

        $domaine = new Domaine();
        $domaine->setNom($data['nom']);

        $entityManager->persist($domaine);
        $entityManager->flush();

        $url = RouteContext::fromRequest($request)
            ->getRouteParser()
            ->urlFor('domaines-list');

        return $response->withHeader('Location', $url)->withStatus(302);
    }

    public function deleteDomaine(Request $request, Response $response, $args)
    {
        $entityManager = $this->container->get(EntityManager::class);
        $domaine = $entityManager->getRepository(Domaine::class)->find($args['id']);

        if ($domaine) {
            $entityManager->remove($domaine);
            $entityManager->flush();
        }

        $url = RouteContext::fromRequest($request)
            ->getRouteParser()
            ->urlFor('domaines-list');

        return $response->withHeader('Location', $url)->withStatus(302);
    }



    public function registerRoutes($app)
    {
        $app->get('/dashboard', [$this, 'dashboard'])->setName('dashboard')->add(UserMiddleware::class);
        $app->get('/domaines', [$this, 'listDomaines'])->setName('domaines-list')->add(UserMiddleware::class);
        $app->post('/domaines/add', [$this, 'addDomaine'])->setName('domaine-add')->add(UserMiddleware::class);
        $app->get('/domaines/delete/{id}', [$this, 'deleteDomaine'])->setName('domaine-delete')->add(UserMiddleware::class);
        
        

    }
}
