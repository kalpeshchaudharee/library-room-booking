<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    protected $table = 'otp';

    protected $fillable = [
        'email', 'code', 'status', 'expire'
    ];
}