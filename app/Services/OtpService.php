<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\DeliveryWorker;
use App\Models\Otp;
use App\Models\Technician;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    private string $apiKey = 'dpEnljbmQhOVJidcdnuKEF:APA91bGsYB6BBTui-ZqNwbawd-byA7l54SWr0AouSoPVp71M-ypcWN3cBifonOE3D__Z_fdh5AFIBtDhTjRZ4N65tKgBl9NJQN8ZidBwspeKnqXiPEoOxFw';
    private string $apiUrl = 'https://www.traccar.org/sms/';

    public function sendOtp(string $phone): bool
    {
        $key = 'send-otp:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return false;
        }
        RateLimiter::hit($key, 60);

        $code = (string) random_int(10000, 99999);

        Otp::create([
            'phone'      => $phone,
            'code'       => $code,
            'is_used'    => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        $message = "شامسونج: كود التحقق هو $code";

        try {
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL            => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode([
                    'to'      => $phone,
                    'message' => $message,
                ]),
                CURLOPT_HTTPHEADER     => [
                    'Authorization: ' . $this->apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT        => 30,
            ]);

            $body       = curl_exec($ch);
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError  = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('[SMS] cURL Error: ' . $curlError);
                return false;
            }

            Log::info('[SMS] Response', [
                'status' => $httpStatus,
                'body'   => $body,
            ]);

            return $httpStatus >= 200 && $httpStatus < 300;

        } catch (\Exception $e) {
            Log::error('[SMS] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @return array{token: string, user: \Illuminate\Database\Eloquent\Model}|string
     */
    public function verifyOtp(string $phone, string $code, string $guard = 'customer'): array|string
    {
        $otp = Otp::where('phone', $phone)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) return 'otp_invalid_or_expired';

        $user = $this->findByPhone($phone, $guard);
        if (!$user) {
            $otp->update(['is_used' => true, 'phone_verified' => true]);
            return 'user_not_found';
        }

        if (in_array($guard, ['technician', 'delivery']) && !$user->is_active) {
            return 'account_deactivated';
        }

        $otp->update(['is_used' => true]);

        return [
            'token' => $user->createToken("{$guard}_token")->plainTextToken,
            'user'  => $user,
        ];
    }

    private function findByPhone(string $phone, string $guard): Customer|Technician|DeliveryWorker|null
    {
        return match ($guard) {
            'technician' => Technician::where('phone', $phone)->first(),
            'delivery'   => DeliveryWorker::where('phone', $phone)->first(),
            default      => Customer::where('phone', $phone)->first(),
        };
    }
}