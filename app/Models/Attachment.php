<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expiration_date' => 'date:Y-m-d',
    ];


    public function leave()
    {
        return $this->morphTo();
    }
    public function employee()
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

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            unlink('storage/'. $attachment->file)
            ;
        });
    }
}
