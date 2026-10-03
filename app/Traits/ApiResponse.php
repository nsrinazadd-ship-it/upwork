<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse{
    protected function coreResponse(bool $success ,string $message
    ,mixed $data =null ,mixed $errors =null ,int $statusCode =Response::HTTP_OK):JsonResponse{
        return response()->json([
            'success'=>$success,
            'message'=>$message,
            'data'=>$data,
            'errors'=>$errors,
        ],$statusCode);
    }

    protected function success(mixed $data=[], string $message='Operation successful.'):JsonResponse{
        return $this->coreResponse(true,$message ,$data,null,Response::HTTP_OK);
    }

    protected function created(mixed $data=[],string $message='Resource created successfully.'):JsonResponse{
        return $this->coreResponse(true, $message,$data,null,Response::HTTP_CREATED);
    }

    protected function noContent(string $message='Resource deleted successfully.'):JsonResponse{
        return $this->coreResponse(true,$message,null,null,Response::HTTP_NO_CONTENT);
    }

    protected function error(string $message='Something went wrong.',int $statusCode =Response::HTTP_BAD_GATEWAY ,mixed $errors=null):JsonResponse{
        return $this->coreResponse(false,$message,null,$errors,$statusCode);
    }

    protected function forbidden(string $message ='You do not have permission to access this resource.'):JsonResponse{
        return $this->error($message,Response::HTTP_FORBIDDEN);
    }

    protected function unauthorized(string $message='Authentication required.'):JsonResponse{
        return $this->error($message,Response::HTTP_UNAUTHORIZED);
    }

}
