<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ActivityLogRepository;

final class ActivityLogService
{
    public function __construct(
        private readonly ActivityLogRepository $repository = new ActivityLogRepository()
    ) {
    }

    public function log(
        array $actor,
        string $entityType,
        string $entityId,
        string $action,
        ?int $companyId = null,
        ?int $establishmentId = null,
        ?string $note = null,
        ?array $metadata = null
    ): void {
        $this->repository->create([
            'actor_user_id' => (int) $actor['id'],
            'company_id' => $companyId,
            'establishment_id' => $establishmentId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'note' => $note,
            'metadata_json' => $metadata !== null ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
    }

    public function recentByCompany(int $companyId, int $limit = 20): array
    {
        return $this->repository->recentByCompany($companyId, $limit);
    }
}
