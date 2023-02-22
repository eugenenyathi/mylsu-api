<?php

namespace App\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    public $table = 'payments';
    public $fillable = ['student_id', 'amount_cleared', 'registered'];
    public $timestamps = false;

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
