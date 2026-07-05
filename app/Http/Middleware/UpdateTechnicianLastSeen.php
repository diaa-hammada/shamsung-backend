<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Technician;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateTechnicianLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Technician|null $technician */
        $technician = $request->user();

        if ($technician instanceof Technician) {
            $technician->timestamps = false;
            $technician->update(['last_seen_at' => now()]);
        }

        return $next($request);
    }
}
