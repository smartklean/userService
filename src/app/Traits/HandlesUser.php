<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\Request;

trait HandlesUser
{
  public function fetchUser(Request $request){
    $params = $request->params ? explode(',', $request->params) : null;

    if($params){
      if($request->search){
        $users = User::where('first_name', 'LIKE', "%{$request->search}%")->orWhere('last_name', 'LIKE', "%{$request->search}%")->orWhere('email', 'LIKE', "%{$request->search}%")->whereIn('id', $params)->orderBy('first_name')->get();
      }else{
        $users = User::whereIn('id', $params)->orderBy('first_name')->get();
      }
    }else{
      if($request->search){
        $users = User::where('first_name', 'LIKE', "%{$request->search}%")->orWhere('last_name', 'LIKE', "%{$request->search}%")->orWhere('email', 'LIKE', "%{$request->search}%")->orderBy('first_name')->get();
      }else{
        $users = User::orderBy('first_name')->get();
      }
    }

    return $users;
  }
}
