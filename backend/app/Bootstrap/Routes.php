<?php
declare(strict_types=1);

namespace App\Bootstrap;

use App\Controllers\AuthController;
use App\Controllers\CompanyController;
use App\Controllers\CategoryController;
use App\Controllers\EstablishmentController;
use App\Controllers\ExpenseTemplateController;
use App\Controllers\HealthController;
use App\Controllers\SummaryController;
use App\Controllers\TransactionController;
use App\Controllers\UserController;

final class Routes
{
    public static function definitions(): array
    {
        $healthController = new HealthController();
        $authController = new AuthController();
        $companyController = new CompanyController();
        $categoryController = new CategoryController();
        $establishmentController = new EstablishmentController();
        $expenseTemplateController = new ExpenseTemplateController();
        $transactionController = new TransactionController();
        $summaryController = new SummaryController();
        $userController = new UserController();

        return [
            ['GET', '/api/health', [$healthController, 'show']],
            ['POST', '/api/auth/login', [$authController, 'login']],
            ['GET', '/api/companies', [$companyController, 'index']],
            ['GET', '/api/companies/{id}', [$companyController, 'show']],
            ['POST', '/api/companies', [$companyController, 'store']],
            ['GET', '/api/categories', [$categoryController, 'index']],
            ['GET', '/api/establishments', [$establishmentController, 'index']],
            ['POST', '/api/establishments', [$establishmentController, 'store']],
            ['GET', '/api/establishments/{id}', [$establishmentController, 'show']],
            ['DELETE', '/api/establishments/{id}', [$establishmentController, 'destroy']],
            ['GET', '/api/establishments/{id}/transactions', [$transactionController, 'index']],
            ['POST', '/api/establishments/{id}/transactions', [$transactionController, 'store']],
            ['DELETE', '/api/transactions/{id}', [$transactionController, 'destroy']],
            ['GET', '/api/establishments/{id}/expense-templates', [$expenseTemplateController, 'index']],
            ['POST', '/api/establishments/{id}/expense-templates', [$expenseTemplateController, 'store']],
            ['POST', '/api/expense-templates/{id}/apply', [$expenseTemplateController, 'apply']],
            ['DELETE', '/api/expense-templates/{id}', [$expenseTemplateController, 'destroy']],
            ['GET', '/api/users', [$userController, 'index']],
            ['POST', '/api/users', [$userController, 'store']],
            ['PUT', '/api/users/{id}', [$userController, 'update']],
            ['DELETE', '/api/users/{id}', [$userController, 'destroy']],
            ['GET', '/api/summary', [$summaryController, 'show']],
        ];
    }
}
