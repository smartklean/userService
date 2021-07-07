<?php

namespace App\Traits;

use Illuminate\Validation\Validator;

trait HandlesJsonResponse
{
  public function jsonValidationError(Validator $validator){
    $data = [
      'errors' => $validator->getMessageBag()->toArray()
    ];

    return $this->jsonResponse(__('response.messages.validation'), __('response.codes.validation_error'), 400, $data, __('response.errors.request'));
  }

  public function jsonResponse($message = '', $code = '00', $status = 200, $data = [], $error = null){
    if($error){
      return response()->json([
        'status' => false,
        'code' => $code,
        'error' => $error,
        'message' => $message,
        'data' => $data,
      ], $status);
    }

    return response()->json([
      'status' => true,
      'code' => $code,
      'message' => $message,
      'data' => $data,
    ], $status);
  }
}
