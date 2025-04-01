<?php

use Slim\App;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Doctrine\ORM\EntityManager;
use App\Domain\User;

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


    $app->get('/login', function (Request $request, Response $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'login.html.twig');
    })->setName('login');
    
    $app->post('/login', function ($request, $response) use ($app) {
        $view = Twig::fromRequest($request);
        $em = $app->getContainer()->get(EntityManager::class);
        try {
            $data = $request->getParsedBody();
            $email = $data['email'];
            $password = $data['password'];
            //use doctrine to find user by email
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            $errors = [];
    
            if ($user === null) {
                $errors[] = 'Utilisateur non trouvé';
            }
    
            if (password_verify($password, $user->getPassword())) {
                $this->container->get('session')->set('user',$user);
                return $response->withJson(['status' => true]);
            } else {
                $errors[] = 'Mot de passe incorrect';
            }

            return $view->render($response, 'login.html.twig',['errors'=> $errors]);
        } catch (Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            $errors[] = 'Erreur interne du serveur '. $e->getMessage();
            return $view->render($response, 'login.html.twig',['errors'=> $errors]);
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
