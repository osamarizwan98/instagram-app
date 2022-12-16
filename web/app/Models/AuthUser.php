<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthUser extends Model
{
    use HasFactory;
    protected $table = 'auth_users';
    protected $fillable = [
        'store_name',
        'access_token',
        'code',
        'app_id',
        'oauth_uid',
        'username',
        'link',
        'account_type',
        'status'
    ];
}
