<?php
use DI\Container;
use Psr\Container\ContainerInterface;
use App\HomeController;
use App\Controller\EntrepriseController;

$container = new Container();

// Définition des contrôleurs dans le conteneur
$container->set(HomeController::class, function (ContainerInterface $container) {
    return new HomeController($container);
});

$container->set(EntrepriseController::class, function (ContainerInterface $container) {
    return new EntrepriseController($container);
});

return $container;