<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginTimestamps extends Model
{
    use HasFactory;

    public $table = 'user_login_timestamps';
    public $timestamps = false;
    protected $fillable = ['id', 'current_stamp', 'previous_stamp'];
}
