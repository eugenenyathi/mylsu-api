<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuburbRoom extends Model
{
    use HasFactory;

    public $table = 'suburb_rooms';
    public $timestamps = false;

    protected $fillable = ['room_number', 'usable', 'con_occupied', 'block_occupied'];
}
