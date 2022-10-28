<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

trait HandlesRequest
{
  private $acceptHeader = 'Accept';
  private $authorizationHeader = 'Authorization';
  private $clientPublicHeader = 'Client-Public';
  private $clientSecretHeader = 'Client-Secret';
  private $contentTypeHeader = 'Content-Type';
  private $refreshTokenHeader = 'Refresh-Token';

  public function call($method, Request $request, $endpoint, $headers = []){
    if(empty($headers)){
      if(!$request->header($this->contentTypeHeader) || !$request->header($this->acceptHeader)){
        return response()->json([
          'status' => false,
          'code' => __('response.codes.error'),
          'error' => __('response.errors.request'),
          'message' => 'Content type or accept header missing from request.',
          'data' => []
        ], 400);
      }

      $headers = [
        $this->contentTypeHeader => $request->header($this->contentTypeHeader),
        $this->acceptHeader => $request->header($this->acceptHeader),
        $this->authorizationHeader => $request->header($this->authorizationHeader),
        $this->refreshTokenHeader => $request->header($this->refreshTokenHeader),
        $this->clientPublicHeader => $request->header($this->clientPublicHeader),
        $this->clientSecretHeader => $request->header($this->clientSecretHeader)
      ];
    }

    switch ($method) {
      case 'GET':
        $response = Http::withHeaders($headers)->get($endpoint, $request->all());
        break;

      case 'POST':
        $response = Http::withHeaders($headers)->post($endpoint, $request->all());
        break;

      case 'PUT':
        $response = Http::withHeaders($headers)->put($endpoint, $request->all());
        break;

      case 'DEL':
        $response = Http::withHeaders($headers)->delete($endpoint, $request->all());
        break;

      default:
        $response = response()->json([
          'status' => false,
          'code' => __('response.codes.error'),
          'error' => __('response.errors.request'),
          'message' => 'Request not supported.',
          'data' => []
        ], 400);
        break;
    }

    return $response;
  }
}
