<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use App\Domain\OffreDeStage;
use App\Domain\Wishlist;
use App\Domain\Entreprise;
use Doctrine\ORM\EntityManager;
use Slim\Routing\RouteContext;
use App\Middlewares\UserMiddleware;

class OffreController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/offres', OffreController::class . ':listOffres')->setName('offre-list')->add(UserMiddleware::class);
        $app->get('/offres/edit/{id}', OffreController::class . ':editOffre')->setName('offre-edit')->add(UserMiddleware::class);
        $app->post('/offres/edit/{id}', OffreController::class . ':editOffre')->add(UserMiddleware::class);
        $app->get('/offres/add', OffreController::class . ':editOffre')->setName('offre-add')->add(UserMiddleware::class);
        $app->post('/offres/add', OffreController::class . ':editOffre')->add(UserMiddleware::class);
        $app->get('/offres/delete/{id}', OffreController::class . ':delete')->setName('offre-delete')->add(UserMiddleware::class);
        $app->post('/wishlist/toggle/{id}', OffreController::class . ':toggleWishlist')->setName('wishlist-toggle')->add(UserMiddleware::class);

    }

    public function listOffres(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $em = $this->container->get(EntityManager::class);
        $offres = $em->getRepository(OffreDeStage::class)->findAll();
        $user = $request->getAttribute('user');
        
        $wishlist = [];
        if ($user) {
            $wishlist = array_map(fn($item) => $item->getOffre()->getId(), $em->getRepository(Wishlist::class)->findBy(['user' => $user]));
        }

        $view = Twig::fromRequest($request);
        return $view->render($response, 'Admin/User/offre-list.html.twig', [
            'offres' => $offres,
            'wishlist' => $wishlist,
        ]);
    }


    public function editOffre(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $entityManager = $this->container->get(EntityManager::class);
        $add = !isset($args['id']);
        $offre = null;

        if ($add) {
            $offre = new OffreDeStage('', '', new \DateTime(), new \DateTime(), 0, new Entreprise());
        } else {
            $offre = $entityManager->getRepository(OffreDeStage::class)->find($args['id']);
            if (!$offre) {
                $response->getBody()->write("Offre de stage non trouvée !");
                return $response->withStatus(404);
            }
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $offre->setTitre($data['titre'] ?? '');
            $offre->setDescription($data['description'] ?? '');
            $offre->setDateDebut(new \DateTime($data['dateDebut'] ?? 'now'));
            $offre->setDateFin(new \DateTime($data['dateFin'] ?? 'now'));
            $offre->setRemuneration(isset($data['remuneration']) ? (float) $data['remuneration'] : 0);

            if (!empty($data['entreprise'])) {
                $entreprise = $entityManager->getRepository(Entreprise::class)->find($data['entreprise']);
                if ($entreprise) {
                    $offre->setEntreprise($entreprise);
                }
            }

            $entityManager->persist($offre);
            $entityManager->flush();

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('offre-list');
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        $entreprises = $entityManager->getRepository(Entreprise::class)->findAll();

        $view = Twig::fromRequest($request);
        return $view->render($response, 'Admin/User/offre-edit.html.twig', [
            'offreEntity' => $offre,
            'add' => $add,
            'entreprises' => $entreprises,
        ]);
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $em = $this->container->get(EntityManager::class);
        $offre = $em->getRepository(OffreDeStage::class)->find($args['id']);

        if ($offre) {
            $em->remove($offre);
            $em->flush();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('offre-list');
        return $response->withHeader('Location', $url)->withStatus(302);
    }

    public function paginatedList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $em = $this->container->get(EntityManager::class);
        $page = (int)($args['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $offres = $em->getRepository(OffreDeStage::class)->findBy([], null, $limit, $offset);
        $totalOffres = $em->getRepository(OffreDeStage::class)->count([]);

        $view = Twig::fromRequest($request);

        return $view->render($response, 'Admin/User/offre-list.html.twig', [
            'offres' => $offres,
            'currentPage' => $page,
            'totalPages' => ceil($totalOffres / $limit),
        ]);
    }
    public function toggleWishlist(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $em = $this->container->get(EntityManager::class);
        $user = $request->getAttribute('user');
        $offre = $em->getRepository(OffreDeStage::class)->find($args['id']);

        if (!$user || !$offre) {
            return $response->withStatus(400);
        }

        $wishlistRepo = $em->getRepository(Wishlist::class);
        $wishlistItem = $wishlistRepo->findOneBy(['user' => $user, 'offre' => $offre]);

        if ($wishlistItem) {
            $em->remove($wishlistItem);
        } else {
            $wishlistItem = new Wishlist($user, $offre);
            $em->persist($wishlistItem);
        }

        $em->flush();
        return $response->withHeader('Location', '/offres')->withStatus(302);
    }
}
