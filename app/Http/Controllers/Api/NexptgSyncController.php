<?php

namespace App\Http\Controllers\Api;

use App\Enums\NexptgApiLogTypeEnum;
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
    ) {}

    /**
     * Handle NexPTG sync POST request
     */
    public function sync(Request $request): JsonResponse
    {
        $apiUser = $request->get('nexptg_api_user');

        try {
            // Validate JSON structure
            $validator = Validator::make($request->all(), [
                'data' => 'required|array',
                'data.reports' => 'sometimes|array',
                'data.history' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                if ($apiUser) {
                    $apiUser->logError(
                        NexptgApiLogTypeEnum::VALIDATION_ERROR->value,
                        400,
                        'Validation failed: '.$validator->errors()->first(),
                        [
                            'validation_errors' => $validator->errors()->toArray(),
                            'endpoint' => $request->path(),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'request_data_sample' => $this->getRequestDataSample($request),
                        ]
                    );
                }

                return response()->json([
                    'error' => 'Invalid request format',
                    'details' => $validator->errors(),
                ], 400);
            }

            $data = $request->input('data');

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

            if ($apiUser) {
                $apiUser->logError(
                    NexptgApiLogTypeEnum::EXCEPTION->value,
                    500,
                    'Sync exception: '.$e->getMessage(),
                    [
                        'exception_type' => get_class($e),
                        'exception_message' => $e->getMessage(),
                        'exception_trace' => $e->getTraceAsString(),
                        'endpoint' => $request->path(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'request_data_sample' => $this->getRequestDataSample($request),
                    ]
                );
            }

            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get a sample of request data for logging (without sensitive information)
     */
    private function getRequestDataSample(Request $request): array
    {
        $data = $request->all();

        // Limit array size and remove potentially large nested arrays
        if (isset($data['data']['reports']) && is_array($data['data']['reports'])) {
            $data['data']['reports'] = [
                'count' => count($data['data']['reports']),
                'sample' => array_slice($data['data']['reports'], 0, 1),
            ];
        }

        if (isset($data['data']['history']) && is_array($data['data']['history'])) {
            $data['data']['history'] = [
                'count' => count($data['data']['history']),
                'sample' => array_slice($data['data']['history'], 0, 1),
            ];
        }

        return $data;
    }
}
