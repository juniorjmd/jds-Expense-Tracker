<?php

declare(strict_types=1);

use App\Controllers\ExpenseController;
use App\Controllers\HealthController;

/** @var Router $router */

$healthController = new HealthController();
$expenseController = new ExpenseController();

$router->get('/health', fn () => $healthController());
$router->get('/transactions', fn () => $expenseController->index());
$router->post('/transactions', fn ($request) => $expenseController->store($request));
