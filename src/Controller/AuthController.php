<?php
namespace App\Controller;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use Doctrine\ORM\EntityManager;
use App\Domain\User;
use Exception;
use Psr\Container\ContainerInterface;

class AuthController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function login(Request $request, Response $response, $args)
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'login.html.twig');
    }

    public function handleLogin(Request $request, Response $response, $args)
    {
        $view = Twig::fromRequest($request);
        $em = $this->container->get(EntityManager::class); 

        try {
            $data = $request->getParsedBody();
            $email = $data['email'];
            $password = $data['password'];
            
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            $errors = [];

            if ($user === null) {
                $errors[] = 'Utilisateur non trouvé';
                return $view->render($response, 'login.html.twig', ['errors' => $errors]);
            }

            if (password_verify($password, $user->getMotDePasse())) {
                $this->container->get('session')->set('user', $user); 
                return $response
                    ->withHeader('Location', '/')
                    ->withStatus(302);
            } else {
                $errors[] = 'Mot de passe incorrect';
            }

            return $view->render($response, 'login.html.twig', ['errors' => $errors]);
        } catch (Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            $errors[] = 'Erreur interne du serveur ' . $e->getMessage();
            return $view->render($response, 'login.html.twig', ['errors' => $errors]);
        }
    }

    public function registerRoutes($app)
    {
        $app->get('/login', [$this, 'login'])->setName('login');
        $app->post('/login', [$this, 'handleLogin']);
    }
}
