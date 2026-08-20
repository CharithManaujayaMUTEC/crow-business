<?php

namespace App\Services\Sms;

use App\Models\Customer;
use App\Models\SmsLog;
use App\Models\SmsSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NotifySmsService
{
    public function send(Customer $customer, string $message, string $type = 'general', ?string $referenceType = null, ?int $referenceId = null): SmsLog
    {
        $settings = SmsSetting::query()->first() ?? new SmsSetting([
            'enabled' => (bool) config('services.notifylk.enabled', false),
            'api_url' => config('services.notifylk.url'),
            'api_user_id' => config('services.notifylk.user_id'),
            'api_key' => config('services.notifylk.api_key'),
            'sender_id' => config('services.notifylk.sender_id', 'Crow.lk'),
            'country_code' => config('services.notifylk.country_code', '94'),
        ]);

        $phone = $this->normalize($customer->phone, $settings->country_code ?: '94');

        if (! $settings->enabled) {
            return SmsLog::create([
                'customer_id' => $customer->id,
                'phone' => $phone,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'message' => $message,
                'status' => 'disabled',
                'sent_at' => now(),
            ]);
        }

        $response = Http::asForm()->post($settings->api_url ?: 'https://app.notify.lk/api/v1/send', [
            'user_id' => $settings->api_user_id,
            'api_key' => $settings->api_key,
            'sender_id' => $settings->sender_id,
            'to' => $phone,
            'message' => $message,
        ]);

        return SmsLog::create([
            'customer_id' => $customer->id,
            'phone' => $phone,
            'type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'message' => $message,
            'status' => $response->successful() ? 'sent' : 'failed',
            'provider_response' => $response->body(),
            'sent_at' => now(),
        ]);
    }

    protected function normalize(string $phone, string $countryCode): string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return $countryCode . substr($phone, 1);
        }

        if (! str_starts_with($phone, $countryCode)) {
            return $countryCode . $phone;
        }

        return $phone;
    }
}
