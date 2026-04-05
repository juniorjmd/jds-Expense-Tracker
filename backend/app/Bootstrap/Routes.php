<?php
declare(strict_types=1);

namespace App\Bootstrap;

use App\Controllers\CategoryController;
use App\Controllers\HealthController;
use App\Controllers\SummaryController;
use App\Controllers\TransactionController;

final class Routes
{
    public static function definitions(): array
    {
        $healthController = new HealthController();
        $categoryController = new CategoryController();
        $transactionController = new TransactionController();
        $summaryController = new SummaryController();

        return [
            ['GET', '/api/health', [$healthController, 'show']],
            ['GET', '/api/categories', [$categoryController, 'index']],
            ['GET', '/api/transactions', [$transactionController, 'index']],
            ['POST', '/api/transactions', [$transactionController, 'store']],
            ['DELETE', '/api/transactions/{id}', [$transactionController, 'destroy']],
            ['GET', '/api/summary', [$summaryController, 'show']],
        ];
    }
}
