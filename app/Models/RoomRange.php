<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomRange extends Model
{
    use HasFactory;

    public $table = 'room_ranges';
    public $timestamps = false;

    protected $fillable = ['first_room', 'last_room', 'side', 'floor', 'suburb_floor_side', 'mbundani_floor_side'];
}
