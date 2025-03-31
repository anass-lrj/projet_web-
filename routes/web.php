<?php

use Slim\App;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\AuthController;

return function (App $app) {

    // Page d'accueil
    /*$app->get('/', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig');
    });*/
    // Page User
    $app->get('/user', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'user.twig', ['username' => 'JohnDoe']);
    });
    // Page Entreprise
    $app->get('/entreprise', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'entreprise.twig', );
    });
    // Page Contact 
    $app->get('/cont', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'contact.twig', );
    });
    // Page Offre
    $app->get('/offre', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'offre.twig', );
    });
    // Page CGU
    $app->get('/CGU', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'CGU.twig', );
    });
    // Page Mentions-légales
    $app->get('/Mentions-légales', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'Mentions-légales.twig', );
    });

    $app->post('/login', function ($request, $response) {
        try {
            // Utiliser getParsedBody() pour récupérer les données POST
            $data = $request->getParsedBody();
            $email = $data['email'];
            $password = $data['password'];
    
            // Recherche l'utilisateur en base de données
            $user = R::findOne('users', 'email = ?', [$email]);
    
            // Si l'utilisateur n'existe pas
            if ($user === null) {
                return $response->withJson(['status' => false, 'message' => 'Utilisateur non trouvé'], 400);
            }
    
            // Vérification du mot de passe
            if (password_verify($password, $user->password)) {
                // Si tout est bon, on ajoute l'utilisateur à la session
                $_SESSION['email'] = $email;
                return $response->withJson(['status' => true]);
            } else {
                return $response->withJson(['status' => false, 'message' => 'Mot de passe incorrect'], 400);
            }
        } catch (Exception $e) {
            // En cas d'erreur interne du serveur
            error_log('Erreur : ' . $e->getMessage()); // Ajoute cette ligne pour loguer les erreurs
            return $response->withJson(['status' => false, 'message' => 'Erreur interne du serveur'], 500);
        }
    });
    
    
    

    /*$app->post('/login', function (Request $request, Response $response, $args) use ($validCredentials) {
        $parsedBody = $request->getParsedBody();
        $email = $parsedBody['email'] ?? '';
        $password = $parsedBody['password'] ?? '';

        if ($email === $validCredentials['email'] && $password === $validCredentials['password']) {
            $_SESSION['email'] = $email;
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Connexion réussie']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Identifiants incorrects']));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });*/
};
