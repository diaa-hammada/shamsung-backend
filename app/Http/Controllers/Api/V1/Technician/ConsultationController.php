<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Technician\ReplyConsultationRequest;
use App\Models\Consultation;
use App\Models\Notification;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function __construct(private readonly FcmService $fcm) {}

    public function index(): JsonResponse
    {
        $consultations = Consultation::with('customer:id,first_name,last_name')
            ->where('consultation_type', 'technician')
            ->latest()
            ->paginate(15);

        return response()->json([
            'message' => 'Technician consultations retrieved successfully',
            'data'    => $consultations,
        ], 200);
    }

    public function reply(ReplyConsultationRequest $request, int $id): JsonResponse
    {
        $technician = $request->user();

        $consultation = Consultation::with('customer')
            ->where('consultation_type', 'technician')
            ->findOrFail($id);

        $consultation->update([
            'technician_id' => $technician->id,
            'reply'         => $request->validated('reply'),
            'status'        => 'answered',
        ]);

        if ($consultation->customer) {
            if ($consultation->customer->fcm_token) {
                $this->fcm->send(
                    $consultation->customer->fcm_token,
                    'Consultation Answered',
                    'A technician has replied to your question.',
                    ['type' => 'consultation', 'id' => (string) $consultation->id],
                );
            }

            Notification::create([
                'customer_id' => $consultation->customer_id,
                'type'        => 'consultation',
                'title'       => 'Consultation Answered',
                'body'        => 'A technician has replied to your question.',
                'data'        => ['consultation_id' => $consultation->id],
            ]);
        }

        return response()->json([
            'message' => 'Consultation replied successfully',
            'data'    => $consultation,
        ], 200);
    }
}
