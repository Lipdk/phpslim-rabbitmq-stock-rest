<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps  = true;
    protected $fillable = ['username', 'password', 'name', 'email'];
    protected $table    = 'users';
}