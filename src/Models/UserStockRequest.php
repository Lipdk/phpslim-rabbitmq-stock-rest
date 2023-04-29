<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStockRequest extends Model
{
    public $timestamps  = true;
    protected $fillable = ['user_id', 'response'];
    protected $table    = 'users_stock_requests';
}