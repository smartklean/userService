<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Traits\HandlesRequest;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
  use HandlesRequest;

  /**
    * Transform the resource into an array.
    *
    * @param \Illuminate\Http\Request $request
    * @return array
    */

    private $headerType = 'application/json';
    private $authorizationServiceUrl = 'authorizationws.url';
    private $roleAndPermissionEndpoint = '/api/v1/authorization/user';

    public function toArray($request){
      $res = $this->call('GET', new Request([]), config($this->authorizationServiceUrl).$this->roleAndPermissionEndpoint.'/'.$this->id.'/role', ['Content-Type' =>$this->headerType, 'Accept' => $this->headerType]);
      $roles = $res->json()['data'];

      //fetch permission
      $res = $this->call('GET', new Request([]), config($this->authorizationServiceUrl).$this->roleAndPermissionEndpoint.'/'.$this->id.'/permission', ['Content-Type' =>$this->headerType, 'Accept' => $this->headerType]);
      $permissions = $res->json()['data'];

      return [
        'id' => $this->id,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
        'email' => $this->email,
        'email_verified_at' => $this->email_verified_at,
        'phone_number' => $this->phone_number,
        'phone_number_verified' => $this->phone_number_verified,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
        'roles' => $roles ?? null,
        'permissions' => $permissions ?? null,
      ];
    }
}
