<?php

declare(strict_types=1);

namespace Esanj\AppService\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

abstract class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected function successResponse(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    protected function errorResponse(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function createdResponse(array $data, string $message = ''): JsonResponse
    {
        $response = ['data' => $data];
        if ($message !== '') {
            $response['message'] = $message;
        }

        return response()->json($response, 201);
    }
}