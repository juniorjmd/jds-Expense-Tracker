<?php
declare(strict_types=1);

namespace App\Modules\Personas;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Personas\Services\PersonasService;

class PersonasController
{
    public function __construct(
        private Request $request,
        private PersonasService $service
    ) {
    }

    public function searchOdooPersonTitle(): void
    {
        try {
            $result = $this->service->searchOdooPersonTitle();
            (new Response())
                ->status(200)
                ->json($result)
                ->send();
        } catch (\Exception $e) {
            (new Response())
                ->status(500)
                ->json(['error' => $e->getMessage()])
                ->send();
        }
    }

    public function getClientMasters(): void
    {
        try {
            $result = $this->service->getClientMasters();
            (new Response())
                ->status(200)
                ->json($result)
                ->send();
        } catch (\Exception $e) {
            (new Response())
                ->status(500)
                ->json(['error' => $e->getMessage()])
                ->send();
        }
    }
}
