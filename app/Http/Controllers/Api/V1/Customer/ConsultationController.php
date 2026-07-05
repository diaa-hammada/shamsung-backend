<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Customer\StoreConsultationRequest;
use App\Models\Consultation;
use App\Models\Notification;
use App\Models\Technician;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $activeTechnicians = Technician::where('is_active', true)->get(['id', 'fcm_token']);

        // FCM push to technicians who have a token
        $fcmTokens = $activeTechnicians->whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
        $this->fcm->sendMultiple(
            $fcmTokens,
            'New Consultation Request',
            'A customer has sent a technical question.',
            ['type' => 'consultation', 'id' => (string) $consultation->id],
        );

        // DB notification — same pattern as maintenance request / stock request triggers
        $customerName    = trim($customer->first_name . ' ' . $customer->last_name);
        $messagePreview  = mb_substr($validated['message'] ?? '', 0, 60);
        $activeTechnicians->each(function (Technician $tech) use ($consultation, $customerName, $messagePreview) {
            Notification::create([
                'technician_id' => $tech->id,
                'type'          => 'new_consultation',
                'title'         => 'استشارة جديدة',
                'body'          => "أرسل {$customerName}: {$messagePreview}",
                'data'          => [
                    'consultation_id' => $consultation->id,
                    'customer_id'     => $consultation->customer_id,
                ],
            ]);
        });

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

        $apiKeys    = explode(',', $keysString);
        $models     = ['gemini-2.5-flash', 'gemini-2.5-flash-lite', 'gemini-flash-latest'];
        $proxyUrl   = config('services.gemini.proxy_url');
        $proxySecret = config('services.gemini.proxy_secret');

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
                    $path = "/v1beta/models/{$modelName}:generateContent?key={$apiKey}";
                    $url  = $proxyUrl ? rtrim($proxyUrl, '/') . $path : "https://generativelanguage.googleapis.com{$path}";

                    $headers = ['Content-Type' => 'application/json'];
                    if ($proxyUrl && $proxySecret) {
                        $headers['X-Proxy-Secret'] = $proxySecret;
                    }

                    $response = Http::withHeaders($headers)
                        ->timeout(30)
                        ->post($url, $requestBody);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                            return $data['candidates'][0]['content']['parts'][0]['text'];
                        }

                        Log::warning('Gemini API: unexpected response shape', [
                            'model' => $modelName,
                            'body'  => $response->body(),
                        ]);
                    } else {
                        Log::warning('Gemini API: non-successful response', [
                            'model'  => $modelName,
                            'status' => $response->status(),
                            'body'   => $response->body(),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Gemini API: request exception', [
                        'model'   => $modelName,
                        'message' => $e->getMessage(),
                    ]);
                    continue;
                }
            }
        }

        return "I'm having trouble connecting to my AI core right now. Please try again later or consult a human technician.";
    }
}
