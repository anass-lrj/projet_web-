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
use App\Domain\OffreDeStage;
use App\Domain\User;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

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
        return $view->render($response, 'Adm
        in/User/domaine-list.html.twig', [
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

    public function offresPostulees(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user'); // Récupérer l'utilisateur connecté
        $entityManager = $this->container->get(EntityManager::class);

        // Récupérer les candidatures de l'utilisateur
        $candidatures = $entityManager->getRepository(\App\Domain\Candidature::class)->findBy(['user' => $user]);

        $view = Twig::fromRequest($request);
        return $view->render($response, 'dashboard/offres_postulees.html.twig', [
            'candidatures' => $candidatures,
            'section' => 'offres_postulees',
        ]);
    }

    public function supprimerCandidature(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user'); // Récupérer l'utilisateur connecté
        $entityManager = $this->container->get(EntityManager::class);

        // Récupérer la candidature par ID
        $candidature = $entityManager->getRepository(\App\Domain\Candidature::class)->find($args['id']);

        // Vérifier si la candidature appartient à l'utilisateur
        if ($candidature && $candidature->getUser() === $user) {
            $entityManager->remove($candidature);
            $entityManager->flush();
        }

        // Rediriger vers la liste des offres postulées
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('offres_postulees');
        return $response->withHeader('Location', $url)->withStatus(302);
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

    // Vérifier si une candidature existe déjà pour cet utilisateur et cette offre
    $existingCandidature = $em->getRepository(Candidature::class)->findOneBy([
        'user' => $user,
        'offre' => $offre,
    ]);

    if ($existingCandidature) {
        $response->getBody()->write("Vous avez déjà postulé à cette offre !");
        return $response->withStatus(400); // Code HTTP 400 : Mauvaise requête
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

    public function registerRoutes($app)
    {
        $app->get('/dashboard', [$this, 'dashboard'])->setName('dashboard')->add(UserMiddleware::class);
        $app->get('/domaines', [$this, 'listDomaines'])->setName('domaines-list')->add(UserMiddleware::class);
        $app->post('/domaines/add', [$this, 'addDomaine'])->setName('domaine-add')->add(UserMiddleware::class);
        $app->get('/domaines/delete/{id}', [$this, 'deleteDomaine'])->setName('domaine-delete')->add(UserMiddleware::class);
        $app->get('/dashboard/offres-postulees', [$this, 'offresPostulees'])->setName('offres_postulees')->add(UserMiddleware::class);
        $app->get('/dashboard/candidature/supprimer/{id}', [$this, 'supprimerCandidature'])->setName('supprimer_candidature')->add(UserMiddleware::class);
        $app->post('/dashboard/candidature/soumettre/{id}', [$this, 'soumettreCandidature'])->setName('soumettre_candidature')->add(UserMiddleware::class);
    }
}
