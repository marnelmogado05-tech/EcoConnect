<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'municipality_id'
    ];

    public function municipalities()
    {
        return $this->belongsTo(Municipality::class);
    }
}
