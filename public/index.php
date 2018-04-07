<?php
/* header('Access-Control-Allow-Origin: http://myseason', true);
header('Access-Control-Allow-Headers:Content-Type, Authorization, Accept, X-Requested-With');
header('Access-Control-Allow-Methods:OPTIONS, TRACE, GET, HEAD, POST, PUT');
*/

require __DIR__ . '/../vendor/autoload.php';

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

$container = $app->getContainer();

// Register dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// require __DIR__ . '/../src/TokenAuth.php';

// \Slim\Slim::registerAutoloader();

// $app->add(new \TokenAuth());

// Register routes
require __DIR__ . '/../src/routes.php';

// Все наше здесь:
require __DIR__ . '/../src/models/login.php';
require __DIR__ . '/../src/models/clients.php';
require __DIR__ . '/../src/models/clients_add.php';
require __DIR__ . '/../src/models/branches.php';
require __DIR__ . '/../src/models/branches_add.php';
require __DIR__ . '/../src/models/halls.php';
require __DIR__ . '/../src/models/halls_add.php';
require __DIR__ . '/../src/models/directions.php';
require __DIR__ . '/../src/models/directions_add.php';
require __DIR__ . '/../src/models/classes.php';
require __DIR__ . '/../src/models/classes_calc.php';
require __DIR__ . '/../src/models/main.php';
require __DIR__ . '/../src/models/season_info.php';
require __DIR__ . '/../src/models/tseasons.php';
require __DIR__ . '/../src/models/seasons_add.php';
require __DIR__ . '/../src/models/seasons_del.php';
require __DIR__ . '/../src/models/seasons_freeze.php';
require __DIR__ . '/../src/models/presence_set.php';
require __DIR__ . '/../src/models/client_info.php';
require __DIR__ . '/../src/models/client_edit.php';
require __DIR__ . '/../src/models/seasons.php';
require __DIR__ . '/../src/models/payments.php';
require __DIR__ . '/../src/models/payments_add.php';
require __DIR__ . '/../src/models/debt_close.php';
require __DIR__ . '/../src/models/invoice.php';
require __DIR__ . '/../src/models/wages.php';
require __DIR__ . '/../src/models/wage_calc_get.php';
$app->run();