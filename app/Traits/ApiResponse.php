<?php


namespace App\Traits;


use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function sendSuccess($message, $data, $extra = [])
    {
        $response = [
            'data' => $data,
            'message' => $message,
            'code' => 200
        ];
        if (count($extra)) {
            $response['extra'] = $extra;
        }
        return response()->json($response, 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    public function sendUnauthorized($message)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => 401
        ], 401)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    public function sendValidationFail($message, array $data = [], $status = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
            'code' => 422
        ], $status)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    public function sendError($message , $code = 400 , $status = 400 )
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code
        ], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    /**
     * @param $message
     * @return JsonResponse
     */
    public function sendNotFound($message)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => 404
        ], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

}
