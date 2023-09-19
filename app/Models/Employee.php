<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
    public function increments()
    {
        return $this->hasMany(Increment::class);
    }
    public function settlements()
    {
        return $this->hasMany(Settlement::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'model');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($record) {
            $record->leaves()->get()->each->delete();
            $record->increments()->get()->each->delete();
            $record->settlements()->get()->each->delete();
            $record->attachments()->get()->each->delete();
        });
    }


    public function getSalaryAttribute()
    {
        return $this->initialSalary + $this->increments->sum('amount');;
    }

    protected function initSalary(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 1000,
            set: fn ($value) => $value * 1000,
        );
    }
}
