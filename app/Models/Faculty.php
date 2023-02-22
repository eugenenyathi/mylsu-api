<?php

namespace App\Models;

use App\Models\Program;
use App\Models\Tuition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faculty extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['id', 'facultyName'];

    public function program()
    {
        return $this->hasMany(Program::class);
    }

    public function tuition()
    {
        return $this->hasOne(Tuition::class);
    }
}
