<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function barangays()
    {
        return $this->hasMany(Barangay::class);
    }

    public function police()
    {
        return $this->hasOne(User::class);
    }
}
