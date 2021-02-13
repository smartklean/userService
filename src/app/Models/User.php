<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'phone_number', 'is_developer',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function getFirstNameAttribute($value){
      return ucwords($value);
    }

    public function setFirstNameAttribute($value){
      return $this->attributes['first_name'] = strtolower(trim($value));
    }

    public function getLastNameAttribute($value){
      return ucwords($value);
    }

    public function setLastNameAttribute($value){
      return $this->attributes['last_name'] = strtolower(trim($value));
    }

    public function setEmailAttribute($value){
      return $this->attributes['email'] = strtolower(trim($value));
    }

    public function setPhoneNumberAttribute($value){
      return $this->attributes['phone_number'] = trim($value);
    }
}
