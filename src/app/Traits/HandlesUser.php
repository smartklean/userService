<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\Request;

trait HandlesUser
{
  public function fetchUser(Request $request){
    $params = $request->params ? explode(',', $request->params) : null;

    if($params){
      $users = User::whereIn('id', $params)->orderBy('first_name')->get();
    }else{
      $users = User::orderBy('first_name')->get();
    }

    return $users;
  }
}
