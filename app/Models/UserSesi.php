<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSesi extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'sesi_tanggal',
        'sesi_status'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
