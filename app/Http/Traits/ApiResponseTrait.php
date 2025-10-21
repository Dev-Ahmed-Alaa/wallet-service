<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait ApiResponseTrait
{
    /**
     * Return success response with data
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return error response
     *
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Operation failed', int $statusCode = 400, array $errors = []): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return resource with additional data
     *
     * @param JsonResource $resource
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function resourceResponse(JsonResource $resource, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        return $resource->additional([
            'status' => 'success',
            'message' => $message,
        ])->response()->setStatusCode($statusCode);
    }
}
