<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStockRequest extends Model
{
    public $timestamps  = true;
    protected $fillable = ['user_id', 'response'];
    protected $table    = 'users_stock_requests';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getResponse()
    {
        if ($this->response) {
            return json_decode($this->response, true);
        }

        return [];
    }
}