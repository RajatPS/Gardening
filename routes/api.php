<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/catalog', fn (): JsonResponse => response()->json([
        'data' => [
            'plants' => ['Indoor', 'Outdoor', 'Flowering', 'Medicinal', 'Air purifying', 'Bonsai'],
            'accessories' => ['Pots', 'Pot stands', 'Fertilizers', 'Pesticides', 'Tools', 'Irrigation'],
        ],
    ]));

    Route::get('/services', fn (): JsonResponse => response()->json([
        'data' => ['Garden setup', 'Plant maintenance', 'Health inspection', 'Pest control', 'Soil replacement', 'Emergency care'],
    ]));

    Route::get('/subscriptions', fn (): JsonResponse => response()->json([
        'data' => ['Basic', 'Standard', 'Premium'],
    ]));

    Route::post('/ai/assistant', fn (): JsonResponse => response()->json([
        'status' => 'queued',
        'message' => 'AI assistant provider integration will be connected in the AI phase.',
    ], 202));

    Route::post('/ai/disease-detection', fn (): JsonResponse => response()->json([
        'status' => 'queued',
        'message' => 'Vision diagnosis provider integration will be connected in the disease detection phase.',
    ], 202));

    Route::get('/reminders', fn (): JsonResponse => response()->json([
        'data' => [
            ['task' => 'Water plant', 'channel' => 'push,email,sms,in_app'],
            ['task' => 'Add fertilizer', 'channel' => 'push,email,sms,in_app'],
            ['task' => 'Service visit reminder', 'channel' => 'push,email,sms,in_app'],
        ],
    ]));
});
