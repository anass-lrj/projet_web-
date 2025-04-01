<?php
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Middlewares\AdminMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use DI\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use App\Middlewares\UserMiddleware;
use App\Controller\HomeController;
use App\Controller\CGUController;
use App\Controller\MentionslegalesController;
use App\Controller\Admin\UserAdminController;
use App\Controller\contactController;
use Slim\Middleware\Session;
use App\Controller\EntrepriseController;
use App\Controller\OffreController;
use App\Controller\AuthController;
use App\Controller\LogoutController;
use App\Controller\DashboardController;


require __DIR__ . '/../vendor/autoload.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Charger le conteneur DI
$container = require __DIR__ . '/../bootstrap.php';
AppFactory::setContainer($container);

// Créer l'application
$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);


// Ajouter le middleware de session après avoir défini le conteneur
$app->add(new Session([
    'name' => 'my_session',
    'autorefresh' => true,
    'lifetime' => '1 hour'
]));

// Ajouter Twig après l'initialisation des sessions
$twig = Twig::create(__DIR__ . '/../src/View', ['cache' => false, 'debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());
$app->add(TwigMiddleware::create($app, $twig));

// Enregistrer les services dans le conteneur
$container->set('view', fn() => $twig);
$container->set(AdminMiddleware::class, fn() => new AdminMiddleware($container));
$container->set(UserMiddleware::class, fn() => new UserMiddleware($container));
$container->set(ResponseFactoryInterface::class, fn() => $app->getResponseFactory());
$container->set('session', fn() => new \SlimSession\Helper());

// Enregistrer les contrôleurs dans le conteneur et les routes associées
$controllers = [
    HomeController::class,
    CGUController::class,
    UserAdminController::class,
    MentionslegalesController::class,
    contactController::class,
    EntrepriseController::class,
    OffreController::class,
    AuthController::class,
    LogoutController::class,
    DashboardController::class,
];

foreach ($controllers as $controller) {
    $container->set($controller, fn() => new $controller($container));
    $container->get($controller)->registerRoutes($app);
}

// Lancer l'application
$app->run();
