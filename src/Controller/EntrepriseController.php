<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use App\Domain\Entreprise;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseFactoryInterface;
use Doctrine\ORM\EntityManager;
use App\Middlewares\UserMiddleware;
use App\Domain\Domaine;

class EntrepriseController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/entreprises', EntrepriseController::class . ':listEntreprises')->setName('entreprises-list')->add(UserMiddleware::class);
        $app->get('/entreprises/edit/{id}', EntrepriseController::class . ':editEntreprise')->setName('entreprise-edit')->add(UserMiddleware::class);
        $app->post('/entreprises/edit/{id}', EntrepriseController::class . ':editEntreprise')->add(UserMiddleware::class);
        $app->get('/entreprises/add', EntrepriseController::class . ':editEntreprise')->setName('entreprises-add')->add(UserMiddleware::class);
        $app->post('/entreprises/add', EntrepriseController::class . ':editEntreprise')->add(UserMiddleware::class);
        $app->get('/entreprises/delete/{id}', EntrepriseController::class . ':delete')->setName('entreprise-delete')->add(UserMiddleware::class);
        $app->get('/entreprises/aperçu/{id}', EntrepriseController::class . ':aperçuEntreprise')->setName('entreprise-aperçu')->add(UserMiddleware::class);
    }


    public function editEntreprise(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $entityManager = $this->container->get(EntityManager::class);
    $session = $this->container->get('session');
    $currentUser = $session->get('user');

    // Vérification du rôle
    if (!$currentUser || !in_array($currentUser->getRole(), ['admin', 'pilote'])) {
        $response->getBody()->write("Accès refusé. Seuls les admins et pilotes peuvent créer ou modifier une entreprise.");
        return $response->withStatus(403);
    }

    $add = !isset($args['id']);

    if ($add) {
        $entreprise = new Entreprise();
    } else {
        $entreprise = $entityManager->getRepository(Entreprise::class)->find($args['id']);

        if (!$entreprise) {
            $response->getBody()->write("Entreprise non trouvée !");
            return $response->withStatus(404);
        }
    }

    if ($request->getMethod() === 'POST') {
        $data = $request->getParsedBody();

        $entreprise->setTitre($data['titre'] ?? '');
        $entreprise->setEmail($data['email'] ?? '');
        $entreprise->setVille($data['ville'] ?? null);
        $entreprise->setDescription($data['description'] ?? null);
        $entreprise->setContactTelephone($data['contactTelephone'] ?? null);
        $entreprise->setNombreStagiaires(isset($data['nombreStagiaires']) ? (int) $data['nombreStagiaires'] : null);
        $entreprise->setEvaluationMoyenne(isset($data['evaluationMoyenne']) ? (float) $data['evaluationMoyenne'] : null);

        $entityManager->persist($entreprise);
        $entityManager->flush();

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


    private function getEntrepriseById($id): ?Entreprise
    {
        return new Entreprise();
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $em = $this->container->get(EntityManager::class);
        $entreprise = $em->getRepository(Entreprise::class)->find($args['id']);
    
        if ($entreprise) {
            $em->remove($entreprise);
            $em->flush();
        }
    
        // Redirection vers la liste des entreprises après suppression
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('entreprises-list');
        return $response->withHeader('Location', $url)->withStatus(302);
    }
    

    public function paginatedList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $em = $this->container->get(EntityManager::class);
        $page = (int)($args['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $entreprises = $em->getRepository(Entreprise::class)->findBy([], null, $limit, $offset);
        $totalEntreprises = $em->getRepository(Entreprise::class)->count([]);

        $view = Twig::fromRequest($request);

        return $view->render($response, 'Admin/User/entreprise-list.html.twig', [
            'entreprises' => $entreprises,
            'currentPage' => $page,
            'totalPages' => ceil($totalEntreprises / $limit),
        ]);
    }
    public function listEntreprises(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $em = $this->container->get(EntityManager::class);
    
    $queryParams = $request->getQueryParams();
    $domaineId = $queryParams['domaine_id'] ?? null;

    $criteria = [];
    if (!empty($domaineId)) {
        $criteria['domaine'] = $domaineId;
    $entityManager = $this->container->get(EntityManager::class);
    
    // Récupérer le terme de recherche depuis la requête
    $searchQuery = $request->getQueryParams()['search'] ?? '';
    
    // Si une recherche est effectuée, filtrez les entreprises par titre
    if ($searchQuery) {
        $entreprises = $entityManager->getRepository(Entreprise::class)
            ->createQueryBuilder('e')
            ->where('e.titre LIKE :search')
            ->setParameter('search', '%' . $searchQuery . '%')
            ->getQuery()
            ->getResult();
    } else {
        // Sinon, récupérer toutes les entreprises
        $entreprises = $entityManager->getRepository(Entreprise::class)->findAll();
    }
    
    // Récupérer l'utilisateur actuel depuis la session
    $session = $this->container->get('session');
    $user = $session->get('user');
    
    // Nombre total d'entreprises pour la pagination
    $totalEntreprises = count($entreprises);
    
    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/entreprise-list.html.twig', [
        'entreprises' => $entreprises,
        'user' => $user,  // Passer l'utilisateur à la vue
        'searchQuery' => $searchQuery,  // Passer la requête de recherche à la vue
        'currentPage' => 1,  // Vous pouvez ajuster la pagination ici si nécessaire
        'totalPages' => ceil($totalEntreprises / 10),  // Calculer le nombre de pages en fonction du nombre total d'entreprises
    ]);
}

    

    $entreprises = $em->getRepository(Entreprise::class)->findBy($criteria);

    // Récupérer tous les domaines pour le menu déroulant
    $domaines = $em->getRepository(Domaine::class)->findAll();

    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/entreprise-list.html.twig', [
        'entreprises' => $entreprises,
        'domaines' => $domaines,
        'selectedDomaine' => $domaineId
    ]);
}

    public function aperçuEntreprise(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $entityManager = $this->container->get(EntityManager::class);

    // Vérifie si l'ID est fourni
    if (!isset($args['id'])) {
        $response->getBody()->write("ID d'entreprise non fourni !");
        return $response->withStatus(400);
    }

    // Recherche de l'entreprise
    $entreprise = $entityManager->getRepository(Entreprise::class)->find($args['id']);

    if (!$entreprise) {
        $response->getBody()->write("Entreprise non trouvée !");
        return $response->withStatus(404);
    }

    // Affichage de la vue avec les détails de l'entreprise
    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/entreprise-view.html.twig', [
        'entrepriseEntity' => $entreprise
    ]);
}
   
}

