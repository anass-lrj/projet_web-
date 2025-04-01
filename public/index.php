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
use App\Controller\EntrepriseController;
use App\Controller\OffreController;



require __DIR__ . '/../vendor/autoload.php';

// Charger le conteneur DI
$container = require __DIR__ . '/../bootstrap.php';
AppFactory::setContainer($container);

$app = AppFactory::create();

// Ajouter Twig
$twig = Twig::create(__DIR__ . '/../src/View', ['cache' => false,'debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());
$app->add(TwigMiddleware::create($app, $twig));

$container->set('view', function () use ($twig) {
    return $twig;
});

$container->set(AdminMiddleware::class, function () use ($container) {
    return new AdminMiddleware($container);
});

$container->set(UserMiddleware::class, function () use ($container) {
    return new UserMiddleware($container);
});

$container->set(ResponseFactoryInterface::class , function () use ($app) {
    return $app->getResponseFactory();
});

$app->add(
    new \Slim\Middleware\Session([
      'name' => 'session',
      'autorefresh' => true,
      'lifetime' => '1 hour',
    ])
);

$container->set('session', function () {
    return new \SlimSession\Helper();
});

// Charger les routes
$routes = require __DIR__ . '/../routes/web.php';
$routes($app);


$container->set(HomeController::class, function () use ($container) {
    return new HomeController($container);
});
$container->get(HomeController::class)->registerRoutes($app);

$container->set(CGUController::class, function () use ($container) {
    return new CGUController($container);
});
$container->get(CGUController::class)->registerRoutes($app);

$container->set(UserAdminController::class, function () use ($container) {
    return new UserAdminController($container);
});
$container->get(UserAdminController::class)->registerRoutes($app);

$container->set(MentionslegalesController::class, function () use ($container) {
    return new MentionslegalesController($container);
});
$container->get(MentionslegalesController::class)->registerRoutes($app);

$container->set(contactController::class, function () use ($container) {
    return new contactController($container);
});
$container->get(contactController::class)->registerRoutes($app);

$container->set(EntrepriseController::class, function () use ($container) {
    return new EntrepriseController($container);
});
$container->get(EntrepriseController::class)->registerRoutes($app);

$container->set(OffreController::class, function () use ($container) {
    return new OffreController($container);
});
$container->get(OffreController::class)->registerRoutes($app);

$app->run();