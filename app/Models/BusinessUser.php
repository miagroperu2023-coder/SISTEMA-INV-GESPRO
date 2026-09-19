<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessUser extends Model
{
    //
    protected $table = 'business_user';

    protected $fillable = [
        'business_id',
        'user_id',
        'rol',
    ];
}
