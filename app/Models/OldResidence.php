<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldResidence extends Model
{
    use HasFactory;
    public $table = 'old_residence';
    public $fillable = ['student_id', 'student_type', 'hostel', 'room', 'part', 'checkedIn', 'checkedOut'];
    public $timestamps = false;
}
