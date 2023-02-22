<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveStudentType extends Model
{
    use HasFactory;

    protected $table = 'active_student_type';
    protected $fillable = ['student_type', 'active'];
    public $timestamps = false;
}
