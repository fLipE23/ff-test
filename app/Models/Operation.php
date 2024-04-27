<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'currency',
        'status',
        'created_at',
        'updated_at',
    ];

}
