<?php

namespace App\Modules\Health;

use App\Core\Http\Request;

class HealthController
{
    public function index(Request $request): array
    {
        return [
            "status" => "ok",
            "time" => date('c')
        ];
    }
}