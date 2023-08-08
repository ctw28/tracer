<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jawaban extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'pertanyaan_id',
        'jawaban',
    ];

    public function pertanyaan()
    {
        return $this->belongsTo('App\Models\Pertanyaan');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function jawabanLainnya()
    {
        return $this->hasOne('App\Models\JawabanLainnya');
    }
}
