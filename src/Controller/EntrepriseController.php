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
use App\Domain\Evaluation;
use App\Domain\Candidature;

class EntrepriseController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/entreprises/edit/{id}', EntrepriseController::class . ':editEntreprise')->setName('entreprise-edit')->add(UserMiddleware::class);
        $app->post('/entreprises/edit/{id}', EntrepriseController::class . ':editEntreprise')->add(UserMiddleware::class);
        $app->get('/entreprises/add', EntrepriseController::class . ':editEntreprise')->setName('entreprises-add')->add(UserMiddleware::class);
        $app->post('/entreprises/add', EntrepriseController::class . ':editEntreprise')->add(UserMiddleware::class);
        $app->get('/entreprises/delete/{id}', EntrepriseController::class . ':delete')->setName('entreprise-delete')->add(UserMiddleware::class);
        $app->get('/entreprises/aperçu/{id}', EntrepriseController::class . ':aperçuEntreprise')->setName('entreprise-aperçu')->add(UserMiddleware::class);
        $app->get('/entreprises/{id}/note', EntrepriseController::class . ':noterEntreprise')->setName('entreprise-note')->add(UserMiddleware::class);
        $app->post('/entreprises/{id}/note', EntrepriseController::class . ':noterEntreprise')->add(UserMiddleware::class);
        $app->get('/entreprises[/{page}]', EntrepriseController::class . ':listEntreprises')->setName('entreprises-list')->add(UserMiddleware::class);        


    }


    public function editEntreprise(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $entityManager = $this->container->get(EntityManager::class);
    $session = $this->container->get('session');
    $currentUser = $session->get('user');

    // Vérification des permissions
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

        // Gestion du domaine
        if (!empty($data['domaine_id'])) {
            $domaine = $entityManager->getRepository(Domaine::class)->find($data['domaine_id']);
            $entreprise->setDomaine($domaine);
        } else {
            $entreprise->setDomaine(null);
        }

        $entityManager->persist($entreprise);
        $entityManager->flush();

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('entreprises-list');
        return $response->withHeader('Location', $url)->withStatus(302);
    }

    $domaines = $entityManager->getRepository(Domaine::class)->findAll();

    $view = Twig::fromRequest($request);
    return $view->render($response, 'Admin/User/entreprise-edit.html.twig', [
        'entrepriseEntity' => $entreprise,
        'add' => $add,
        'domaines' => $domaines, 
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

        $searchQuery = $queryParams['search'] ?? '';
        $domaineId = $queryParams['domaine_id'] ?? null;

        $qb = $em->getRepository(Entreprise::class)->createQueryBuilder('e');

        // Appliquer le filtrage par domaine si un domaine est sélectionné
        if (!empty($domaineId)) {
            $qb->andWhere('e.domaine = :domaine')
                ->setParameter('domaine', $domaineId);
        }

        // Appliquer la recherche par nom si une requête est donnée
        if (!empty($searchQuery)) {
            $qb->andWhere('e.titre LIKE :search')
                ->setParameter('search', '%' . $searchQuery . '%');
        }

        // Pagination
        $page = isset($args['page']) ? (int)$args['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $qb->setMaxResults($limit)->setFirstResult($offset);
        $entreprises = $qb->getQuery()->getResult();

        // Récupérer le nombre total d'entreprises
        $totalEntreprises = $em->getRepository(Entreprise::class)->count([]);
        $totalPages = ceil($totalEntreprises / $limit);

        // Récupérer tous les domaines pour le filtre
        $domaines = $em->getRepository(Domaine::class)->findAll();

        // Récupérer l'utilisateur actuel depuis la session
        $session = $this->container->get('session');
        $user = $session->get('user');

        $view = Twig::fromRequest($request);
        return $view->render($response, 'Admin/User/entreprise-list.html.twig', [
            'entreprises' => $entreprises,
            'domaines' => $domaines,
            'selectedDomaine' => $domaineId,
            'searchQuery' => $searchQuery,
            'user' => $user,
            'currentPage' => $page,
            'totalPages' => $totalPages,
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
    
        // Récupérer les offres associées à cette entreprise
        $offres = $entreprise->getOffresDeStage();
    
        // Calculer le nombre total de candidatures pour toutes les offres
        $nombreTotalCandidatures = 0;
        foreach ($offres as $offre) {
            $nombreTotalCandidatures += $entityManager->getRepository(Candidature::class)->count(['offre' => $offre]);
        }
    
        // Affichage de la vue avec les détails de l'entreprise
        $view = Twig::fromRequest($request);
        return $view->render($response, 'Admin/User/entreprise-view.html.twig', [
            'entrepriseEntity' => $entreprise,
            'offres' => $offres,
            'nombreOffres' => count($offres), // Nombre d'offres
            'nombreTotalCandidatures' => $nombreTotalCandidatures, // Nombre total de candidatures
        ]);
    }
    public function noterEntreprise(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $entityManager = $this->container->get(EntityManager::class);

        // Récupérer l'entreprise par son ID
        $id = $args['id'];
        $entreprise = $entityManager->getRepository(Entreprise::class)->find($id);

        if (!$entreprise) {
            $response->getBody()->write('Entreprise non trouvée.');
            return $response->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $note = isset($data['note']) ? (float) $data['note'] : null;
            $commentaire = $data['commentaire'] ?? null;

            // Vérifier que la note est valide
            if ($note === null || $note < 0 || $note > 20) {
                $response->getBody()->write('Note invalide. Elle doit être comprise entre 0 et 20.');
                return $response->withStatus(400);
            }

            // Ajouter une nouvelle évaluation
            $evaluation = new Evaluation();
            $evaluation->setEntreprise($entreprise);
            $evaluation->setNote($note);
            $evaluation->setCommentaire($commentaire);
            $evaluation->setCreatedAt(new \DateTime());

            $entityManager->persist($evaluation);

            // Recalculer la moyenne des évaluations
            $totalNotes = 0;
            $evaluations = $entityManager->getRepository(Evaluation::class)->findBy(['entreprise' => $entreprise]);

            foreach ($evaluations as $eval) {
                $totalNotes += $eval->getNote();
            }

            $evaluationCount = count($evaluations);
            $evaluationMoyenne = $evaluationCount > 0 ? $totalNotes / $evaluationCount : null;
            $entreprise->setEvaluationMoyenne($evaluationMoyenne);
            $entityManager->flush();

            // Rediriger vers la liste des entreprises
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('entreprises-list');
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        // Afficher le formulaire de notation
        $view = Twig::fromRequest($request);
        return $view->render($response, 'entreprise-note.html.twig', [
            'entreprise' => $entreprise,
        ]);
    }
}