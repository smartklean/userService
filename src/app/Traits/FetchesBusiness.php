<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

trait FetchesBusiness
{
  public function fetchBusiness(Request $request, $businessId){
    $res = $this->call('GET', $request, config('businessws.url').'/api/v1/business/'.$businessId, [
      'Content-Type' => 'application/json',
      'Accept' => 'application/json'
    ]);

    return $res->status() == 200 ? json_decode($res->getBody())->data : null;
  }
}
