<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expiration_date' => 'datetime',
    ];

    public function leave()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->morphTo();
    }
    public function inc()
    {
        return $this->morphTo();
    }
    public function settlement()
    {
        return $this->morphTo();
    }
}
