<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database\Connection;
use App\Repositories\CompanyAccessLogRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CompanySettingRepository;
use App\Repositories\CompanySubscriptionRepository;
use App\Repositories\EstablishmentRepository;
use App\Repositories\PlanRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use InvalidArgumentException;
use Throwable;

final class CompanyService
{
    public function __construct(
        private readonly CompanyRepository $companies = new CompanyRepository(),
        private readonly CompanyAccessLogRepository $accessLogs = new CompanyAccessLogRepository(),
        private readonly CompanySettingRepository $settings = new CompanySettingRepository(),
        private readonly CompanySubscriptionRepository $subscriptions = new CompanySubscriptionRepository(),
        private readonly EstablishmentRepository $establishments = new EstablishmentRepository(),
        private readonly PlanRepository $plans = new PlanRepository(),
        private readonly TransactionRepository $transactions = new TransactionRepository(),
        private readonly UserRepository $users = new UserRepository(),
        private readonly AuthService $auth = new AuthService(),
        private readonly ActivityLogService $activityLogs = new ActivityLogService()
    ) {
    }

    public function list(array $actor): array
    {
        $this->assertSuperuser($actor);

        return array_map(static function (array $row): array {
            return [
                'id' => (string) $row['id'],
                'name' => (string) $row['name'],
                'description' => (string) ($row['description'] ?? ''),
                'planCode' => (string) ($row['plan_code'] ?? ''),
                'planName' => (string) ($row['plan_name'] ?? ''),
                'subscriptionStatus' => (string) ($row['subscription_status'] ?? ''),
                'currencyCode' => (string) ($row['currency_code'] ?? ''),
                'timezone' => (string) ($row['timezone'] ?? ''),
                'establishmentsCount' => (int) ($row['establishments_count'] ?? 0),
                'usersCount' => (int) ($row['users_count'] ?? 0),
                'createdAt' => (string) $row['created_at'],
            ];
        }, $this->companies->all());
    }

    public function overview(array $actor, int $companyId, ?string $month = null): array
    {
        $this->assertSuperuser($actor);

        $normalizedMonth = preg_match('/^\d{4}-\d{2}$/', (string) $month) === 1 ? (string) $month : date('Y-m');
        $company = $companyId > 0 ? $this->companies->findWithCounts($companyId) : null;
        if ($company === null) {
            throw new InvalidArgumentException('La empresa solicitada no existe.');
        }

        $this->accessLogs->create([
            'actor_user_id' => (int) $actor['id'],
            'company_id' => $companyId,
            'action' => 'view_company_overview',
            'note' => 'Ingreso explicito al detalle operativo de la empresa.',
        ]);

        $totals = $this->transactions->monthlyTotals($normalizedMonth, $companyId);
        $income = (float) ($totals['income'] ?? 0);
        $expense = (float) ($totals['expense'] ?? 0);

        return [
            'company' => [
                'id' => (string) $company['id'],
                'name' => (string) $company['name'],
                'description' => (string) ($company['description'] ?? ''),
                'planCode' => (string) ($company['plan_code'] ?? ''),
                'planName' => (string) ($company['plan_name'] ?? ''),
                'subscriptionStatus' => (string) ($company['subscription_status'] ?? ''),
                'currencyCode' => (string) ($company['currency_code'] ?? ''),
                'timezone' => (string) ($company['timezone'] ?? ''),
                'dateFormat' => (string) ($company['date_format'] ?? ''),
                'brandingName' => (string) ($company['branding_name'] ?? ''),
                'createdAt' => (string) $company['created_at'],
                'establishmentsCount' => (int) ($company['establishments_count'] ?? 0),
                'usersCount' => (int) ($company['users_count'] ?? 0),
            ],
            'settings' => [
                'currencyCode' => (string) ($company['currency_code'] ?? ''),
                'timezone' => (string) ($company['timezone'] ?? ''),
                'dateFormat' => (string) ($company['date_format'] ?? ''),
                'brandingName' => (string) ($company['branding_name'] ?? ''),
            ],
            'subscription' => [
                'status' => (string) ($company['subscription_status'] ?? ''),
                'planCode' => (string) ($company['plan_code'] ?? ''),
                'planName' => (string) ($company['plan_name'] ?? ''),
            ],
            'summary' => [
                'month' => $normalizedMonth,
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
                'breakdown' => array_map(static function (array $row): array {
                    $rowIncome = (float) ($row['income'] ?? 0);
                    $rowExpense = (float) ($row['expense'] ?? 0);

                    return [
                        'id' => (string) $row['id'],
                        'companyId' => (string) ($row['company_id'] ?? ''),
                        'companyName' => (string) ($row['company_name'] ?? ''),
                        'name' => (string) $row['name'],
                        'income' => $rowIncome,
                        'expense' => $rowExpense,
                        'balance' => $rowIncome - $rowExpense,
                    ];
                }, $this->transactions->monthlyBreakdown($normalizedMonth, $companyId)),
            ],
            'establishments' => array_map(static function (array $row): array {
                $rowIncome = (float) ($row['income'] ?? 0);
                $rowExpense = (float) ($row['expense'] ?? 0);

                return [
                    'id' => (string) $row['id'],
                    'companyId' => (string) $row['company_id'],
                    'companyName' => (string) ($row['company_name'] ?? ''),
                    'name' => (string) $row['name'],
                    'description' => (string) ($row['description'] ?? ''),
                    'createdAt' => (string) $row['created_at'],
                    'transactionCount' => (int) ($row['transaction_count'] ?? 0),
                    'income' => $rowIncome,
                    'expense' => $rowExpense,
                    'balance' => $rowIncome - $rowExpense,
                ];
            }, $this->establishments->all($normalizedMonth, $companyId)),
            'users' => array_map([$this->auth, 'mapUser'], $this->users->allByCompany($companyId)),
            'accessLogs' => array_map(static function (array $row): array {
                return [
                    'id' => (string) $row['id'],
                    'companyId' => (string) $row['company_id'],
                    'actorUserId' => (string) $row['actor_user_id'],
                    'actorName' => (string) ($row['actor_name'] ?? ''),
                    'actorEmail' => (string) ($row['actor_email'] ?? ''),
                    'action' => (string) $row['action'],
                    'note' => (string) ($row['note'] ?? ''),
                    'createdAt' => (string) $row['created_at'],
                ];
            }, $this->accessLogs->recentByCompany($companyId)),
            'activityLogs' => array_map(static function (array $row): array {
                return [
                    'id' => (string) $row['id'],
                    'companyId' => isset($row['company_id']) ? (string) $row['company_id'] : null,
                    'establishmentId' => isset($row['establishment_id']) ? (string) $row['establishment_id'] : null,
                    'actorUserId' => (string) $row['actor_user_id'],
                    'actorName' => (string) ($row['actor_name'] ?? ''),
                    'actorEmail' => (string) ($row['actor_email'] ?? ''),
                    'entityType' => (string) $row['entity_type'],
                    'entityId' => (string) $row['entity_id'],
                    'action' => (string) $row['action'],
                    'note' => (string) ($row['note'] ?? ''),
                    'createdAt' => (string) $row['created_at'],
                ];
            }, $this->activityLogs->recentByCompany($companyId)),
        ];
    }

    public function create(array $actor, array $payload): array
    {
        $this->assertSuperuser($actor);

        $companyName = trim((string) ($payload['name'] ?? ''));
        $adminName = trim((string) ($payload['adminName'] ?? ''));
        $adminEmail = strtolower(trim((string) ($payload['adminEmail'] ?? '')));
        $adminPassword = (string) ($payload['adminPassword'] ?? '');

        if ($companyName === '' || $adminName === '' || $adminEmail === '' || $adminPassword === '') {
            throw new InvalidArgumentException('Empresa y usuario administrador son obligatorios.');
        }

        if ($this->users->emailExists($adminEmail)) {
            throw new InvalidArgumentException('El email del administrador ya esta registrado.');
        }

        $pdo = Connection::get();
        try {
            $pdo->beginTransaction();

            $company = $this->companies->create([
                'name' => $companyName,
                'description' => trim((string) ($payload['description'] ?? '')),
            ]);

            $defaultPlan = $this->plans->findDefault();
            if ($defaultPlan === null) {
                throw new InvalidArgumentException('No existe un plan por defecto activo.');
            }

            $this->subscriptions->createDefault((int) $company['id'], (int) $defaultPlan['id']);
            $this->settings->createDefault((int) $company['id'], $companyName);

            $admin = $this->users->create([
                'company_id' => (int) $company['id'],
                'full_name' => $adminName,
                'email' => $adminEmail,
                'password_hash' => password_hash($adminPassword, PASSWORD_BCRYPT),
                'role' => 'administrador',
                'assigned_establishments' => [],
            ]);

            $pdo->commit();

            $this->activityLogs->log(
                $actor,
                'company',
                (string) $company['id'],
                'company_created',
                (int) $company['id'],
                null,
                'Empresa creada con administrador inicial.',
                [
                    'companyName' => $companyName,
                    'adminEmail' => $adminEmail,
                    'planCode' => (string) $defaultPlan['code'],
                ]
            );

            return [
                'company' => [
                    'id' => (string) $company['id'],
                    'name' => (string) $company['name'],
                    'description' => (string) ($company['description'] ?? ''),
                    'planCode' => (string) $defaultPlan['code'],
                    'planName' => (string) $defaultPlan['name'],
                    'subscriptionStatus' => 'active',
                    'currencyCode' => 'COP',
                    'timezone' => 'America/Bogota',
                    'createdAt' => (string) $company['created_at'],
                ],
                'adminUser' => $this->auth->mapUser($admin),
            ];
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function assertSuperuser(array $actor): void
    {
        if (($actor['role'] ?? '') !== 'superusuario') {
            throw new InvalidArgumentException('Solo el superusuario puede administrar empresas.');
        }
    }
}
