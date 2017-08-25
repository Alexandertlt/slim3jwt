<?php
require __DIR__ . '/../vendor/autoload.php';

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

$container = $app->getContainer();

// Register dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

require __DIR__ . '/../src/TokenAuth.php';

// \Slim\Slim::registerAutoloader();

$app->add(new \TokenAuth());

// Register routes
require __DIR__ . '/../src/routes.php';

// Все наше здесь:
require __DIR__ . '/../src/models/login.php';

$app->run();
