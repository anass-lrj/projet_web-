<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use App\Domain\OffreDeStage;
use App\Domain\Wishlist;
use App\Domain\Entreprise;
use App\Domain\Competence;
use Doctrine\ORM\EntityManager;
use Slim\Routing\RouteContext;
use App\Middlewares\UserMiddleware;
use App\Domain\Candidature;


class OffreController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/offres', OffreController::class . ':list')->setName('offre-list')->add(UserMiddleware::class);
        $app->get('/offres/edit/{id}', OffreController::class . ':editOffre')->setName('offre-edit')->add(UserMiddleware::class);
        $app->post('/offres/edit/{id}', OffreController::class . ':editOffre')->add(UserMiddleware::class);
        $app->get('/offres/add', OffreController::class . ':editOffre')->setName('offre-add')->add(UserMiddleware::class);
        $app->post('/offres/add', OffreController::class . ':editOffre')->add(UserMiddleware::class);
        $app->get('/offres/delete/{id}', OffreController::class . ':delete')->setName('offre-delete')->add(UserMiddleware::class);
        $app->post('/wishlist/toggle/{id}', OffreController::class . ':toggleWishlist')->setName('wishlist-toggle')->add(UserMiddleware::class);
        $app->get('/wishlist', OffreController::class . ':wishlist')->setName('wishlist')->add(UserMiddleware::class);
        $app->get('/wishlist/remove/{id}', OffreController::class . ':removeFromWishlist')->setName('wishlist-remove')->add(UserMiddleware::class);
        $app->get('/competences', OffreController::class . ':manageCompetences')->setName('competence-manage')->add(UserMiddleware::class);
        $app->post('/competences/add', OffreController::class . ':addCompetence')->setName('competence-add')->add(UserMiddleware::class);
        $app->post('/competences/delete/{id}', OffreController::class . ':deleteCompetence')->setName('competence-delete')->add(UserMiddleware::class);
        $app->get('/offres/list[/{page}]', OffreController::class . ':list')->setName('offre-paginated-list')->add(UserMiddleware::class);
        $app->get('/offres/postuler/{id}', OffreController::class . ':postuler')->setName('offre-postuler')->add(UserMiddleware::class);
        $app->get('/offres/details/{id}', OffreController::class . ':details')->setName('offre-details')->add(UserMiddleware::class);
        $app->post('/offres/postuler/{id}', OffreController::class . ':soumettreCandidature')->setName('offre-postuler-submit')->add(UserMiddleware::class);
    }



    public function listOffres(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->list($request, $response, $args);
    }


    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $em = $this->container->get(EntityManager::class);
        $queryParams = $request->getQueryParams();
    
        // Récupération des filtres
        $competenceId = $queryParams['competence'] ?? null;
        $offreName = $queryParams['nom'] ?? null;
        $entrepriseId = $queryParams['entreprise'] ?? null;
    
        // Construction de la requête
        $queryBuilder = $em->getRepository(OffreDeStage::class)->createQueryBuilder('o');
    
        if ($competenceId) {
            $queryBuilder->join('o.competences', 'c')
                         ->andWhere('c.id = :competenceId')
                         ->setParameter('competenceId', $competenceId);
        }
    
        if ($offreName) {
            $queryBuilder->andWhere('o.titre LIKE :offreName')
                         ->setParameter('offreName', '%' . $offreName . '%');
        }
    
        if ($entrepriseId) {
            $queryBuilder->andWhere('o.entreprise = :entrepriseId')
                         ->setParameter('entrepriseId', $entrepriseId);
        }
    
        // Pagination
        $page = isset($args['page']) ? (int)$args['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
    
        $queryBuilder->setMaxResults($limit)->setFirstResult($offset);
        $offres = $queryBuilder->getQuery()->getResult();
    
        // Récupérer le nombre total d'offres
        $totalOffres = $em->getRepository(OffreDeStage::class)->count([]);
        $totalPages = ceil($totalOffres / $limit);
    
        // Récupération des données pour les filtres
        $competences = $em->getRepository(Competence::class)->findAll();
        $entreprises = $em->getRepository(Entreprise::class)->findAll();
    
        $view = Twig::fromRequest($request);
    
        return $view->render($response, 'Admin/User/offre-list.html.twig', [
            'offres' => $offres,
            'competences' => $competences,
            'entreprises' => $entreprises,
            'filters' => [
                'competence' => $competenceId,
                'nom' => $offreName,
                'entreprise' => $entrepriseId,
            ],
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }


    public function editOffre(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $user = $request->getAttribute('user');

    // Vérifier si l'utilisateur est admin ou pilote
    if (!$user || !in_array($user->getRole(), ['admin', 'pilote'])) {
        $response->getBody()->write("Accès refusé !");
        return $response->withStatus(403);
    }

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

    // Gestion des compétences
    $offre->getCompetences()->clear();
    if (!empty($data['competences']) && !in_array('null', $data['competences'])) {
        foreach ($data['competences'] as $competenceId) {
            $competence = $entityManager->getRepository(Competence::class)->find($competenceId);
            if ($competence) {
                $offre->addCompetence($competence);
            }
        }
    }

    $entityManager->persist($offre);
    $entityManager->flush();

    $routeParser = RouteContext::fromRequest($request)->getRouteParser();
    $url = $routeParser->urlFor('offre-list');
    return $response->withHeader('Location', $url)->withStatus(302);
}

    $entreprises = $entityManager->getRepository(Entreprise::class)->findAll();

    $competences = $entityManager->getRepository(Competence::class)->findAll();
    
    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/offre-edit.html.twig', [
        'offreEntity' => $offre,
        'add' => $add,
        'entreprises' => $entreprises,
        'competences' => $competences,
    ]);

}

public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $user = $request->getAttribute('user');

    // Vérifier si l'utilisateur est admin ou pilote
    if (!$user || !in_array($user->getRole(), ['admin', 'pilote'])) {
        $response->getBody()->write("Accès refusé !");
        return $response->withStatus(403);
    }

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
    $user = $request->getAttribute('user'); // Récupérer l'utilisateur connecté
    $offre = $em->getRepository(OffreDeStage::class)->find($args['id']); // Récupérer l'offre

    if (!$user || !$offre) {
        return $response->withStatus(400); // Mauvaise requête si l'utilisateur ou l'offre est introuvable
    }

    $wishlistRepo = $em->getRepository(Wishlist::class);
    $wishlistItem = $wishlistRepo->findOneBy(['user' => $user, 'offre' => $offre]);

    if ($wishlistItem) {
        // Si l'offre est déjà dans la wishlist, la supprimer
        $em->remove($wishlistItem);
    } else {
        // Sinon, l'ajouter à la wishlist
        $wishlistItem = new Wishlist($user, $offre);
        $em->persist($wishlistItem);
    }

    $em->flush();

    // Rediriger vers la page précédente
    $referer = $request->getHeaderLine('Referer');
    return $response->withHeader('Location', $referer ?: '/offres')->withStatus(302);
}

public function wishlist(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $em = $this->container->get(EntityManager::class);
    $user = $request->getAttribute('user'); // Récupère l'utilisateur connecté

    // Vérifiez si l'utilisateur est connecté
    if (!$user) {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('login'); // Redirigez vers la page de connexion si non connecté
        return $response->withHeader('Location', $url)->withStatus(302);
    }

    // Récupérez les offres dans la wishlist de l'utilisateur
    $wishlistItems = $em->getRepository(Wishlist::class)->findBy(['user' => $user]);

    // Rendre la vue Twig
    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/wishlist.html.twig', [
        'wishlistItems' => $wishlistItems,
    ]);
}

public function removeFromWishlist(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $em = $this->container->get(EntityManager::class);
    $user = $request->getAttribute('user'); // Récupère l'utilisateur connecté
    $offre = $em->getRepository(OffreDeStage::class)->find($args['id']);

    if (!$user || !$offre) {
        return $response->withStatus(400);
    }

    $wishlistRepo = $em->getRepository(Wishlist::class);
    $wishlistItem = $wishlistRepo->findOneBy(['user' => $user, 'offre' => $offre]);

    if ($wishlistItem) {
        $em->remove($wishlistItem);
        $em->flush();
    }

    $routeParser = RouteContext::fromRequest($request)->getRouteParser();
    $url = $routeParser->urlFor('wishlist');
    return $response->withHeader('Location', $url)->withStatus(302);
}

public function manageCompetences(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $user = $request->getAttribute('user');

    // Vérifiez si l'utilisateur est admin ou pilote
    if (!$user || !in_array($user->getRole(), ['admin', 'pilote'])) {
        $response->getBody()->write("Accès refusé !");
        return $response->withStatus(403);
    }

    $em = $this->container->get(EntityManager::class);
    $competences = $em->getRepository(Competence::class)->findAll();

    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/competence-manage.html.twig', [
        'competences' => $competences,
    ]);
}

public function addCompetence(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $user = $request->getAttribute('user');

    // Vérifiez si l'utilisateur est admin ou pilote
    if (!$user || !in_array($user->getRole(), ['admin', 'pilote'])) {
        $response->getBody()->write("Accès refusé !");
        return $response->withStatus(403);
    }

    $data = $request->getParsedBody();
    $competenceName = $data['nom'] ?? '';

    if (!empty($competenceName)) {
        $em = $this->container->get(EntityManager::class);
        $competence = new Competence($competenceName);
        $em->persist($competence);
        $em->flush();
    }

    $routeParser = RouteContext::fromRequest($request)->getRouteParser();
    $url = $routeParser->urlFor('competence-manage');
    return $response->withHeader('Location', $url)->withStatus(302);
}


public function deleteCompetence(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $user = $request->getAttribute('user');

    // Vérifiez si l'utilisateur est admin ou pilote
    if (!$user || !in_array($user->getRole(), ['admin', 'pilote'])) {
        $response->getBody()->write("Accès refusé !");
        return $response->withStatus(403);
    }

    $em = $this->container->get(EntityManager::class);
    $competence = $em->getRepository(Competence::class)->find($args['id']);

    if ($competence) {
        $em->remove($competence);
        $em->flush();
    }

    $routeParser = RouteContext::fromRequest($request)->getRouteParser();
    $url = $routeParser->urlFor('competence-manage');
    return $response->withHeader('Location', $url)->withStatus(302);
}

// Removed duplicate method to avoid fatal error

public function postuler(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $user = $request->getAttribute('user'); // Récupérer l'utilisateur connecté
    $em = $this->container->get(EntityManager::class);

    // Vérifier si l'utilisateur est connecté
    if (!$user) {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('login'); // Rediriger vers la page de connexion si non connecté
        return $response->withHeader('Location', $url)->withStatus(302);
    }

    // Récupérer l'offre à partir de l'ID
    $offre = $em->getRepository(OffreDeStage::class)->find($args['id']);
    if (!$offre) {
        $response->getBody()->write("Offre non trouvée !");
        return $response->withStatus(404);
    }

    // Rendre la vue Twig pour le formulaire
    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/formulaire.html.twig', [
        'user' => $user,
        'offre' => $offre,
    ]);
}


public function details(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $em = $this->container->get(EntityManager::class);

    // Récupérer l'offre à partir de l'ID
    $offre = $em->getRepository(OffreDeStage::class)->find($args['id']);
    if (!$offre) {
        $response->getBody()->write("Offre non trouvée !");
        return $response->withStatus(404);
    }

    // Compter le nombre de candidatures associées à cette offre
    $nombreCandidatures = $em->getRepository(Candidature::class)->count(['offre' => $offre]);

    // Rendre la vue Twig pour afficher les détails de l'offre
    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/offre-details.html.twig', [
        'offre' => $offre,
        'nombreCandidatures' => $nombreCandidatures, // Transmettre le nombre de candidatures à la vue
    ]);
}

public function soumettreCandidature(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $user = $request->getAttribute('user'); // Récupérer l'utilisateur connecté
    $em = $this->container->get(EntityManager::class);

    // Vérifier si l'utilisateur est connecté
    if (!$user) {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('login'); // Rediriger vers la page de connexion si non connecté
        return $response->withHeader('Location', $url)->withStatus(302);
    }

    // Récupérer l'offre à partir de l'ID
    $offre = $em->getRepository(OffreDeStage::class)->find($args['id']);
    if (!$offre) {
        $response->getBody()->write("Offre non trouvée !");
        return $response->withStatus(404);
    }

    // Récupérer les données du formulaire
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();

    // Créer une nouvelle candidature
    $candidature = new Candidature();
    $candidature->setUser($user);
    $candidature->setOffre($offre);
    $candidature->setNom($data['nom']);
    $candidature->setPrenom($data['prenom']);
    $candidature->setAdresse($data['adresse']);
    $candidature->setLettreMotivation($data['lettreMotivation']);
    $candidature->setTelephone($data['telephone'] ?? null);
    $candidature->setPortfolio($data['portfolio'] ?? null);
    $candidature->setMessage($data['message'] ?? null);

    // Gérer l'upload du CV
    if (isset($uploadedFiles['cv']) && $uploadedFiles['cv']->getError() === UPLOAD_ERR_OK) {
        $cv = $uploadedFiles['cv'];
        $filename = sprintf('%s_%s', uniqid(), $cv->getClientFilename());
        $cv->moveTo(__DIR__ . '/../../uploads/cv/' . $filename);
        $candidature->setCv($filename);
    }

    // Sauvegarder la candidature
    $em->persist($candidature);
    $em->flush();

    // Rediriger vers la liste des offres
    $routeParser = RouteContext::fromRequest($request)->getRouteParser();
    $url = $routeParser->urlFor('offre-list');
    return $response->withHeader('Location', $url)->withStatus(302);
}

}