<?php

namespace App\Models;

use App\Models\Faculty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tuition extends Model
{
    use HasFactory;

    public $table = 'tuition';
    public $timestamps = false;


    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}
