<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsPackage;
use Illuminate\Http\JsonResponse;

class SmsPackageController extends Controller
{
    public function index(): JsonResponse
    {
        $packages = SmsPackage::query()
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->orderBy('sms_count')
            ->get([
                'id',
                'name',
                'sms_count',
                'price',
                'validity_days',
                'description',
            ])
            ->map(function (SmsPackage $package): array {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'sms_count' => $package->sms_count,
                    'price' => (float) $package->price,
                    'currency' => 'LKR',
                    'validity_days' => $package->validity_days,
                    'description' => $package->description,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }
}
