<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
  /**
    * Transform the resource into an array.
    *
    * @param \Illuminate\Http\Request $request
    * @return array
    */

    public function toArray($request){
      return [
        'id' => $this->id,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
        'email' => $this->email,
        'email_verified_at' => $this->email_verified_at,
        'phone_number' => $this->phone_number,
        'otp' => $this->otp,
        'phone_number_verified' => $this->phone_number_verified,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at
      ];
    }
}
