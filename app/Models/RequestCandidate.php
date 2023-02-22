<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestCandidate extends Model
{
    use HasFactory;

    public $table = 'request_candidates';
    public $timestamps = false;
    protected $fillable = ['requester_id', 'selected_roomie', 'gender', 'selection_confirmed'];
}
