<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NexptgSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NexptgSyncController extends Controller
{
    public function __construct(
        protected NexptgSyncService $syncService
    ) {
    }

    /**
     * Handle NexPTG sync POST request
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            // Validate JSON structure
            $validator = Validator::make($request->all(), [
                'data' => 'required|array',
                'data.reports' => 'sometimes|array',
                'data.history' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Invalid request format',
                    'details' => $validator->errors(),
                ], 400);
            }

            $data = $request->input('data');
            $apiUser = $request->get('nexptg_api_user');

            if (! $apiUser) {
                return response()->json([
                    'error' => 'API user not found',
                ], 500);
            }

            // Sync data
            $this->syncService->sync($data, $apiUser);

            // Return success response (no username/password in response)
            return response()->json([
                'status' => 'success',
                'message' => 'Synchronization completed successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('NexPTG sync error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }
}
