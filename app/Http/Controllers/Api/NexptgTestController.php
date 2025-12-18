<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NexptgApiUser;
use App\Services\NexptgSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NexptgTestController extends Controller
{
    public function __construct(
        protected NexptgSyncService $syncService
    ) {
    }

    /**
     * Test endpoint - Statik bir API user ile test isteği atar (GET)
     */
    public function test(Request $request): JsonResponse
    {
        try {
            // Statik test credentials
            $testUsername = 'olexfilms_HVdijjbGK0264rw4';
            $testPassword = 'password';

            // API user'ı bul
            $apiUser = NexptgApiUser::where('username', $testUsername)
                ->where('is_active', true)
                ->first();

            if (! $apiUser) {
                return response()->json([
                    'error' => 'API user not found',
                    'message' => "API user with username '{$testUsername}' not found or inactive",
                ], 404);
            }

            // Test verisi - sample_real_data.json dosyasının içeriği
            $sampleDataPath = base_path('docs/nexptg/sample_real_data.json');
            
            if (!file_exists($sampleDataPath)) {
                return response()->json([
                    'error' => 'Sample data file not found',
                    'message' => "File not found: {$sampleDataPath}",
                ], 404);
            }

            $testDataJson = file_get_contents($sampleDataPath);
            $testData = json_decode($testDataJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid JSON in sample data file',
                    'message' => 'JSON decode error: ' . json_last_error_msg(),
                ], 400);
            }

            // Basic Auth header oluştur
            $credentials = base64_encode($testUsername . ':' . $testPassword);
            
            // Kendi API'mize istek at
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
                'Content-Type' => 'application/json',
            ])->post(config('app.url') . '/api/nexptg/sync', $testData);

            return response()->json([
                'status' => 'success',
                'message' => 'Test request sent',
                'api_user' => [
                    'id' => $apiUser->id,
                    'username' => $apiUser->username,
                    'user_id' => $apiUser->user_id,
                ],
                'response' => $response->json(),
                'response_status' => $response->status(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('NexPTG test error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

