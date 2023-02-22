<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Residence extends Model
{
    use HasFactory;

    public $table = 'residence';
    public $fillable = ['student_id', 'student_type', 'hostel', 'room', 'part', 'checkedIn', 'checkedOut'];
    public $timestamps = false;
}
