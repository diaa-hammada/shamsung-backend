<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Customer\StoreConsultationRequest;
use App\Models\Consultation;
use App\Models\Technician;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConsultationController extends Controller
{
    public function __construct(private readonly FcmService $fcm) {}

    public function store(StoreConsultationRequest $request): JsonResponse
    {
        $validated   = $request->validated();
        $customer    = $request->user();
        $imagePath   = null;
        $imageBase64 = null;

        if ($request->hasFile('image')) {
            $imagePath   = $request->file('image')->store('consultations', 'public');
            $imageBase64 = base64_encode(file_get_contents($request->file('image')->path()));
        }

        $consultation = Consultation::create([
            'customer_id'       => $customer->id,
            'consultation_type' => $validated['consultation_type'],
            'message'           => $validated['message'] ?? null,
            'image_path'        => $imagePath,
            'status'            => $validated['consultation_type'] === 'ai' ? 'ai_answered' : 'pending',
        ]);

        if ($consultation->consultation_type === 'ai') {
            $aiReply = $this->callGeminiApi($validated['message'] ?? null, $imageBase64);
            $consultation->update(['reply' => $aiReply]);

            return response()->json([
                'message' => 'AI Consultation completed',
                'data'    => $consultation,
            ], 200);
        }

        $tokens = Technician::where('is_active', true)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        $this->fcm->sendMultiple(
            $tokens,
            'New Consultation Request',
            'A customer has sent a technical question.',
            ['type' => 'consultation', 'id' => (string) $consultation->id],
        );

        return response()->json([
            'message' => 'Consultation sent to technician successfully',
            'data'    => $consultation,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $consultations = Consultation::where('customer_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'message' => 'Consultations retrieved successfully',
            'data'    => $consultations,
        ], 200);
    }

    private function callGeminiApi(?string $text, ?string $imageBase64): string
    {
        $keysString = config('services.gemini.keys');
        if (! $keysString) {
            return 'AI service is currently unavailable (Missing API Keys).';
        }

        $apiKeys = explode(',', $keysString);
        $models  = ['gemini-2.5-flash-lite', 'gemini-flash-latest'];

        $parts = [];
        if ($imageBase64) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => 'image/jpeg',
                    'data'      => $imageBase64,
                ],
            ];
        }

        $promptText = $text
            ? "Act as an expert technician. Answer in user's language. User: {$text}"
            : "Act as an expert technician. Identify the issue in this image and give advice in user's language.";

        $parts[]     = ['text' => $promptText];
        $requestBody = ['contents' => [['parts' => $parts]]];

        foreach ($apiKeys as $apiKey) {
            $apiKey = trim($apiKey);
            foreach ($models as $modelName) {
                try {
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";

                    $response = Http::withHeaders(['Content-Type' => 'application/json'])
                        ->timeout(15)
                        ->post($url, $requestBody);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                            return $data['candidates'][0]['content']['parts'][0]['text'];
                        }
                    }
                } catch (\Exception) {
                    continue;
                }
            }
        }

        return "I'm having trouble connecting to my AI core right now. Please try again later or consult a human technician.";
    }
}
