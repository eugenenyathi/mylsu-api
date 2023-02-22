<?php

namespace App\Models;

use App\Models\Faculty;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory;

    public $table = 'programmes';
    public $timestamps = false;

    public function profile()
    {
        return $this->hasMany(Profile::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}
