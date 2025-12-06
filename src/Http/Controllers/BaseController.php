<?php

namespace Esanj\AppService\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

abstract class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    
    protected function successResponse(array $data, int $status = 200)
    {
        return response()->json($data, $status);
    }

    protected function errorResponse(string $message, int $status = 400, array $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    protected function noContentResponse()
    {
        return response()->json([], 204);
    }
}