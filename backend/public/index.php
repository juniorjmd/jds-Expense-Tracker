<?php

declare(strict_types=1);

use App\Core\Routing\Router;

require_once __DIR__ . '/../app/Config/bootstrap.php';

$router = new Router();
require_once __DIR__ . '/../routes/api.php';

$router->dispatch();
