<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchException extends Model
{
    use HasFactory;

    protected $table = 'search_exception';
    protected $fillable = ['program_id'];
    public $timestamps = false;
}
