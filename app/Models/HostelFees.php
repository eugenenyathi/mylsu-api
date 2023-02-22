<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelFees extends Model
{
    use HasFactory;

    public $table = "hostel_fees";
    public $timestamps = false;
}
